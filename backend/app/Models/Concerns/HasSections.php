<?php

namespace App\Models\Concerns;

use App\Models\PageSection;
use App\Sections\SectionRegistry;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSections
{
    public static function bootHasSections(): void
    {
        static::deleting(fn ($model) => $model->sections()->delete());
    }

    public function sections(): MorphMany
    {
        return $this->morphMany(PageSection::class, 'sectionable')->orderBy('sort_order');
    }

    public function sectionsSnapshot(): array
    {
        $sections = $this->relationLoaded('sections') ? $this->sections : $this->sections()->get();

        return $sections->map(fn (PageSection $section): array => [
            'type' => $section->type,
            'label' => $section->label,
            'anchor' => $section->anchor,
            'is_visible' => (bool) $section->is_visible,
            'data' => $section->data,
        ])->values()->all();
    }

    public function replaceSections(array $sections): void
    {
        $this->sections()->delete();
        foreach (array_values($sections) as $index => $section) {
            $this->sections()->create([
                'type' => $section['type'],
                'label' => $section['label'] ?? null,
                'anchor' => $section['anchor'] ?? null,
                'is_visible' => (bool) ($section['is_visible'] ?? true),
                'sort_order' => $index,
                'data' => $section['data'] ?? [],
            ]);
        }
        $this->unsetRelation('sections');
    }

    public function previewSections(array $sections): void
    {
        $this->setRelation('sections', collect($sections)->values()->map(fn (array $section, int $index) => (new PageSection)->forceFill([
            'id' => -($index + 1),
            'type' => $section['type'],
            'label' => $section['label'] ?? null,
            'anchor' => $section['anchor'] ?? null,
            'is_visible' => (bool) ($section['is_visible'] ?? true),
            'sort_order' => $index,
            'data' => $section['data'] ?? [],
        ])));
    }

    public function sectionMediaIds(): array
    {
        $ids = [];
        foreach ($this->sections()->get() as $section) {
            $ids = array_merge($ids, SectionRegistry::mediaIds($section->type, $section->data ?? []));
        }

        return $ids;
    }
}
