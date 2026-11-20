<?php

namespace App\Models;

use App\Models\Concerns\CreatesSlugRedirects;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasSections;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\TracksMediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use CreatesSlugRedirects, HasPublication, HasSections, HasSeo, TracksMediaUsage;

    protected $fillable = [
        'name', 'slug', 'tagline', 'summary', 'body', 'image_id', 'sort_order',
        'hidden_blocks',
        'meta_title', 'meta_description', 'og_image_id', 'canonical_url', 'noindex',
        'status', 'review_state', 'published_at', 'scheduled_for',
    ];

    protected function casts(): array
    {
        return ['hidden_blocks' => 'array'];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class)->orderBy('sort_order');
    }

    public function centres(): HasMany
    {
        return $this->hasMany(Centre::class)->orderBy('sort_order');
    }

    public function faculty(): HasMany
    {
        return $this->hasMany(Faculty::class)->orderBy('sort_order');
    }

    public function publicPath(): ?string
    {
        return '/schools/'.$this->slug;
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
