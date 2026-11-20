<?php

namespace App\Models;

use App\Models\Concerns\CreatesSlugRedirects;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\TracksMediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partner extends Model
{
    use CreatesSlugRedirects, HasPublication, HasSeo, TracksMediaUsage;

    protected $fillable = [
        'partnership_category_id', 'name', 'slug', 'summary', 'story', 'highlight', 'website_url',
        'logo_id', 'image_id', 'is_featured', 'sort_order',
        'meta_title', 'meta_description', 'og_image_id', 'canonical_url', 'noindex',
        'status', 'review_state', 'published_at', 'scheduled_for',
    ];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PartnershipCategory::class, 'partnership_category_id');
    }

    public function logo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function publicPath(): ?string
    {
        return $this->story ? '/partnerships/stories/'.$this->slug : null;
    }
}
