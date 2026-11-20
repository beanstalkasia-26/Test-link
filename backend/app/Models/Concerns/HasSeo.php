<?php

namespace App\Models\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasSeo
{
    public function initializeHasSeo(): void
    {
        $this->mergeCasts(['noindex' => 'boolean']);
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_id');
    }

    public function seo(array $defaults = []): array
    {
        $image = $this->ogImage ?? ($defaults['image'] ?? null);

        return [
            'title' => $this->meta_title ?: ($defaults['title'] ?? $this->displayTitle()),
            'description' => $this->meta_description ?: ($defaults['description'] ?? null),
            'image' => $image?->url('lg'),
            'canonical' => $this->canonical_url ?: ($this->publicPath() !== null ? url($this->publicPath()) : null),
            'noindex' => (bool) $this->noindex,
            'type' => $defaults['type'] ?? 'website',
        ];
    }
}
