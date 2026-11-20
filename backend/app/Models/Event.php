<?php

namespace App\Models;

use App\Models\Concerns\CreatesSlugRedirects;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\TracksMediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use CreatesSlugRedirects, HasPublication, HasSeo, TracksMediaUsage;

    public const Categories = [
        'conference' => 'Conference/Symposium',
        'seminar' => 'Seminar/Lecture',
        'masterclass' => 'Masterclass',
        'outreach' => 'Outreach Programme',
    ];

    protected $fillable = [
        'category', 'school_id', 'centre_id', 'title', 'slug', 'subtitle', 'summary', 'overview', 'outcomes', 'speaker',
        'starts_at', 'ends_at', 'venue', 'registration_url', 'image_id',
        'hidden_blocks',
        'meta_title', 'meta_description', 'og_image_id', 'canonical_url', 'noindex',
        'status', 'review_state', 'published_at', 'scheduled_for',
    ];

    protected function casts(): array
    {
        return ['hidden_blocks' => 'array', 'outcomes' => 'array', 'speaker' => 'array', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centre::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function mediaColumns(): array
    {
        return ['image_id', 'og_image_id'];
    }

    public function additionalMediaReferences(): array
    {
        return ['speaker' => array_filter([(int) ($this->speaker['photo'] ?? 0)])];
    }

    public function publicPath(): ?string
    {
        return '/events/'.$this->slug;
    }
}
