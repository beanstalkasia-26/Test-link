<?php

namespace App\Models;

use App\Models\Concerns\CreatesSlugRedirects;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\TracksMediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOpening extends Model
{
    use CreatesSlugRedirects, HasPublication, HasSeo, TracksMediaUsage;

    protected $table = 'jobs_openings';

    public const Categories = [
        'internship' => 'Internships & Apprenticeships',
        'academic' => 'Academic Positions',
        'research' => 'Research Positions',
        'executive' => 'Executive & Administrative Positions',
    ];

    protected $fillable = [
        'category', 'department', 'title', 'slug', 'summary', 'description', 'closes_on', 'mode', 'sort_order',
        'meta_title', 'meta_description', 'og_image_id', 'canonical_url', 'noindex',
        'status', 'review_state', 'published_at', 'scheduled_for',
    ];

    protected function casts(): array
    {
        return ['closes_on' => 'date'];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function isOpen(): bool
    {
        return $this->closes_on === null || $this->closes_on->endOfDay()->isFuture();
    }

    public function publicPath(): ?string
    {
        return '/careers/'.$this->slug;
    }
}
