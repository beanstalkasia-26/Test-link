<?php

namespace App\Models;

use App\Models\Concerns\CreatesSlugRedirects;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\TracksMediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Faculty extends Model
{
    use CreatesSlugRedirects, HasPublication, HasSeo, TracksMediaUsage;

    protected $table = 'faculty';

    public const Categories = ['residential' => 'Residential', 'visiting' => 'Visiting'];

    protected $fillable = [
        'school_id', 'category', 'name', 'slug', 'designation', 'department', 'email', 'phone', 'summary', 'bio',
        'qualifications', 'experience', 'research', 'publications', 'awards', 'research_interests', 'profile_url', 'photo_id', 'is_featured', 'sort_order',
        'hidden_blocks',
        'meta_title', 'meta_description', 'og_image_id', 'canonical_url', 'noindex',
        'status', 'review_state', 'published_at', 'scheduled_for',
    ];

    protected function casts(): array
    {
        return ['hidden_blocks' => 'array', 'qualifications' => 'array', 'experience' => 'array', 'awards' => 'array', 'is_featured' => 'boolean'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_id');
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class)->withPivot('role');
    }

    public function publicPath(): ?string
    {
        return '/faculty/'.$this->slug;
    }

    public function snapshotRelations(): array
    {
        return ['programs' => $this->programs()->pluck('programs.id')->all()];
    }

    public function restoreRelations(array $relations): void
    {
        if (array_key_exists('programs', $relations)) {
            $this->programs()->sync($relations['programs']);
        }
    }

    public function previewRelations(array $relations): void
    {
        $this->setRelation('programs', Program::query()->whereIn('id', $relations['programs'] ?? [])->get());
    }
}
