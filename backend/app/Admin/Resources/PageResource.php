<?php

namespace App\Admin\Resources;

use App\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageResource extends Resource
{
    public function model(): string
    {
        return Page::class;
    }

    public function slug(): string
    {
        return 'pages';
    }

    public function label(): string
    {
        return 'Pages';
    }

    public function singular(): string
    {
        return 'Page';
    }

    public function navigationGroup(): string
    {
        return 'Website';
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Page title', 'required' => true, 'max' => 200],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'title', 'help' => 'The last part of the address. Changing it on a published page creates a redirect.'],
            ['name' => 'parent_id', 'type' => 'belongsTo', 'source' => 'page_ids', 'label' => 'Parent page', 'help' => 'Nest the page under another page (affects its URL).'],
            ['name' => 'template', 'type' => 'select', 'label' => 'Template', 'required' => true, 'options' => Page::Templates],
            ['name' => 'excerpt', 'type' => 'textarea', 'label' => 'Summary', 'max' => 500, 'help' => 'Used in search results on the site.'],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order'],
            ['name' => 'sections', 'type' => 'sections', 'label' => 'Page sections', 'group' => 'Sections'],
        ];
    }

    public function searchable(): array
    {
        return ['title', 'path'];
    }

    public function with(): array
    {
        return ['workingCopy'];
    }

    public function defaultSort(): array
    {
        return ['path', 'asc'];
    }

    public function defaults(): array
    {
        return ['template' => 'default', 'sections' => []];
    }

    public function columns(): array
    {
        return [['key' => 'title', 'label' => 'Title'], ['key' => 'path', 'label' => 'Address']];
    }

    public function row(Model $record): array
    {
        return parent::row($record) + ['path' => '/'.$record->path];
    }

    public function rules(Request $request, ?Model $record): array
    {
        $rules = parent::rules($request, $record);
        $rules['parent_id'][] = Rule::notIn(array_filter([$record?->getKey()]));

        return $rules;
    }

    protected function slugUniqueRule(Request $request, ?Model $record): mixed
    {
        return Rule::unique('pages', 'slug')
            ->where(fn ($query) => $request->input('parent_id') ? $query->where('parent_id', $request->input('parent_id')) : $query->whereNull('parent_id'))
            ->ignore($record?->getKey());
    }

    public function extract(array $validated, ?Model $record): array
    {
        [$attributes, $relations, $media] = parent::extract($validated, $record);

        if (($attributes['template'] ?? null) === 'home') {
            $attributes['slug'] = $attributes['slug'] ?: 'home';
        }

        return [$attributes, $relations, $media];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'sections.*.data.*.required' => 'This section field is required.',
            'parent_id.not_in' => 'A page cannot be its own parent.',
        ];
    }
}
