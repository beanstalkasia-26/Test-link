<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\PageResponse;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Rules\SafeUrl;
use App\Support\AuditLogger;
use App\Support\OptionSources;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends Controller
{
    public function index(): Response
    {
        return PageResponse::render('admin/menus/Index', [
            'menus' => Menu::query()->withCount('items')->orderBy('id')->get()->map(fn (Menu $menu) => [
                'id' => $menu->id,
                'name' => $menu->name,
                'location' => Menu::Locations[$menu->location] ?? $menu->location,
                'items_count' => $menu->items_count,
            ]),
        ]);
    }

    public function edit(Menu $menu): Response
    {
        $items = $menu->items()->get();
        $tree = function (?int $parentId) use (&$tree, $items): array {
            return $items->where('parent_id', $parentId)->values()->map(fn (MenuItem $item) => [
                'key' => 'i'.$item->id,
                'label' => $item->label,
                'page_id' => $item->page_id,
                'url' => $item->url,
                'opens_in_new_tab' => $item->opens_in_new_tab,
                'is_visible' => $item->is_visible,
                'children' => $tree($item->id),
            ])->all();
        };

        return PageResponse::render('admin/menus/Edit', [
            'menu' => ['id' => $menu->id, 'name' => $menu->name, 'location' => Menu::Locations[$menu->location] ?? $menu->location],
            'items' => $tree(null),
            'pages' => collect(OptionSources::get('page_ids'))->map(fn ($label, $id) => ['value' => $id, 'label' => $label])->values(),
            'maxDepth' => $this->maxDepth($menu),
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $maxDepth = $this->maxDepth($menu);
        $rules = ['items' => ['present', 'array', 'max:80']];
        $messages = [];
        $prefix = 'items.*';
        for ($depth = 1; $depth <= $maxDepth; $depth++) {
            $rules += [
                "$prefix.label" => ['required', 'string', 'max:80'],
                "$prefix.page_id" => ['nullable', 'integer', Rule::exists('pages', 'id')],
                "$prefix.url" => ['nullable', 'string', new SafeUrl],
                "$prefix.opens_in_new_tab" => ['boolean'],
                "$prefix.is_visible" => ['boolean'],
                "$prefix.children" => $depth < $maxDepth ? ['array', 'max:40'] : ['prohibited'],
            ];
            $messages["$prefix.label.required"] = 'Every menu item needs a label.';
            $messages["$prefix.children.prohibited"] = "This menu supports {$maxDepth} level(s) of items.";
            $prefix .= '.children.*';
        }
        $request->validate($rules, $messages);

        DB::transaction(function () use ($request, $menu): void {
            $menu->items()->delete();
            $save = function (array $items, ?int $parentId) use (&$save, $menu): void {
                foreach (array_values($items) as $index => $item) {
                    $model = $menu->items()->create([
                        'parent_id' => $parentId,
                        'label' => $item['label'],
                        'page_id' => $item['page_id'] ?? null,
                        'url' => empty($item['page_id']) ? ($item['url'] ?? null) : null,
                        'opens_in_new_tab' => (bool) ($item['opens_in_new_tab'] ?? false),
                        'is_visible' => (bool) ($item['is_visible'] ?? true),
                        'sort_order' => $index,
                    ]);
                    if (! empty($item['children'])) {
                        $save($item['children'], $model->id);
                    }
                }
            };
            $save($request->input('items', []), null);
            $menu->touch();
        });

        AuditLogger::log('navigation.updated', $menu, 'Updated menu "'.$menu->name.'"');

        return back()->with('success', 'Menu saved and published.');
    }

    private function maxDepth(Menu $menu): int
    {
        return match ($menu->location) {
            'primary', 'utility' => 3,
            default => 1,
        };
    }
}
