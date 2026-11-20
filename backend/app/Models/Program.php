<?php

namespace App\Models;

use App\Models\Concerns\CreatesSlugRedirects;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasSections;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\TracksMediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Program extends Model
{
    use CreatesSlugRedirects, HasPublication, HasSections, HasSeo, TracksMediaUsage;

    public const Levels = [
        'undergraduate' => 'Undergraduate',
        'postgraduate' => 'Postgraduate',
        'executive' => 'Executive & professional',
        'technical' => 'Technical education',
    ];

    protected $fillable = [
        'school_id', 'title', 'slug', 'level', 'award', 'summary', 'body', 'mode', 'location',
        'intake', 'application_deadline', 'duration', 'duration_detail', 'apply_url', 'image_id',
        'is_featured', 'sort_order',
        'hidden_blocks', 'details',
        'meta_title', 'meta_description', 'og_image_id', 'canonical_url', 'noindex',
        'status', 'review_state', 'published_at', 'scheduled_for',
    ];

    protected function casts(): array
    {
        return ['hidden_blocks' => 'array', 'details' => 'array', 'application_deadline' => 'date', 'is_featured' => 'boolean'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function faculty(): BelongsToMany
    {
        return $this->belongsToMany(Faculty::class)->withPivot('role');
    }

    public const IptpSlugPrefix = 'iptp-';

    public const IptpBasePath = '/school-of-technical-education/iptp';

    public const IptpOverviewSlug = 'iptp';

    public function isIptp(): bool
    {
        return str_starts_with((string) $this->slug, self::IptpSlugPrefix);
    }

    public function publicPath(): ?string
    {
        if ($this->slug === self::IptpOverviewSlug) {
            return self::IptpBasePath;
        }
        if ($this->isIptp()) {
            return self::IptpBasePath.'/'.substr($this->slug, strlen(self::IptpSlugPrefix));
        }

        return '/programs/'.$this->slug;
    }

    public function levelLabel(): string
    {
        return self::Levels[$this->level] ?? ucfirst((string) $this->level);
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
