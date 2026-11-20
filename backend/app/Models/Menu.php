<?php

namespace App\Models;

use App\Models\Concerns\FlushesPublicCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use FlushesPublicCache;

    public const Locations = [
        'utility' => 'Header – top utility bar',
        'primary' => 'Header – main navigation',
        'footer_programs' => 'Footer – Programs column',
        'footer_schools' => 'Footer – Schools and Centres column',
        'footer_partnerships' => 'Footer – Partnerships column',
        'footer_quick_links' => 'Footer – Quick links column',
        'legal' => 'Footer – legal links',
    ];

    protected $fillable = ['name', 'location'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id');
    }

    public function tree(): array
    {
        $items = $this->items()->with('page:id,path,status,published_at')->where('is_visible', true)->get();

        $build = function (?int $parentId) use (&$build, $items): array {
            return $items->where('parent_id', $parentId)->values()->map(fn (MenuItem $item): array => [
                'id' => $item->id,
                'label' => $item->label,
                'url' => $item->resolvedUrl(),
                'newTab' => (bool) $item->opens_in_new_tab,
                'external' => $item->isExternal(),
                'children' => $build($item->id),
            ])->filter(fn (array $item): bool => $item['url'] !== null || $item['children'] !== [])->values()->all();
        };

        return $build(null);
    }
}
