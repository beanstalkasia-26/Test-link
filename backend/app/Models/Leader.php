<?php

namespace App\Models;

use App\Models\Concerns\CreatesSlugRedirects;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\TracksMediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leader extends Model
{
    use CreatesSlugRedirects, HasPublication, HasSeo, TracksMediaUsage;

    public const Groups = [
        'board' => 'Board of Directors',
        'advisory' => 'Advisory Council',
        'management' => 'Management Team',
        'consultancy' => 'Consultancy leadership team',
    ];

    protected $fillable = [
        'group', 'name', 'slug', 'designation', 'organisation', 'summary', 'bio', 'achievements', 'email', 'photo_id', 'sort_order',
        'hidden_blocks',
        'meta_title', 'meta_description', 'og_image_id', 'canonical_url', 'noindex',
        'status', 'review_state', 'published_at', 'scheduled_for',
    ];

    protected function casts(): array
    {
        return ['hidden_blocks' => 'array', 'achievements' => 'array'];
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_id');
    }

    public function publicPath(): ?string
    {
        return '/about/people/'.$this->slug;
    }
}
