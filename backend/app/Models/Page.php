<?php

namespace App\Models;

use App\Models\Concerns\CreatesSlugRedirects;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasSections;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\TracksMediaUsage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use CreatesSlugRedirects, HasPublication, HasSections, HasSeo, TracksMediaUsage;

    public const Templates = [
        'default' => 'Standard page',
        'home' => 'Homepage',
        'legal' => 'Legal / policy page',
        'landing' => 'School / centre landing page',
    ];

    protected $fillable = [
        'parent_id', 'title', 'slug', 'template', 'excerpt', 'sort_order',
        'meta_title', 'meta_description', 'og_image_id', 'canonical_url', 'noindex',
        'status', 'review_state', 'published_at', 'scheduled_for',
    ];

    protected static function booted(): void
    {
        static::saving(function (Page $page): void {
            $page->path = $page->computePath();
        });

        static::saved(function (Page $page): void {
            if ($page->wasChanged('path')) {
                $page->children()->get()->each->save();
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('sort_order');
    }

    public function computePath(): string
    {
        if ($this->template === 'home') {
            return '';
        }

        $prefix = $this->parent_id ? (Page::query()->whereKey($this->parent_id)->value('path') ?? '') : '';

        return ltrim($prefix.'/'.$this->slug, '/');
    }

    public function publicPath(): ?string
    {
        return '/'.ltrim((string) ($this->path ?? $this->computePath()), '/');
    }

    public function scopeHome(Builder $query): Builder
    {
        return $query->where('template', 'home');
    }

    public function snapshotRelations(): array
    {
        return ['sections' => $this->sectionsSnapshot()];
    }

    public function restoreRelations(array $relations): void
    {
        if (array_key_exists('sections', $relations)) {
            $this->replaceSections($relations['sections']);
            $this->syncMediaUsage();
        }
    }

    public function previewRelations(array $relations): void
    {
        $this->previewSections($relations['sections'] ?? []);
    }

    public function additionalMediaReferences(): array
    {
        return ['sections' => $this->sectionMediaIds()];
    }
}
