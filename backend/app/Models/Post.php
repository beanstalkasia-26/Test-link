<?php

namespace App\Models;

use App\Models\Concerns\CreatesSlugRedirects;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasSections;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\TracksMediaUsage;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use CreatesSlugRedirects, HasPublication, HasSections, HasSeo, TracksMediaUsage;

    public const Types = [
        'news' => ['label' => 'News', 'prefix' => 'news'],
        'blog' => ['label' => 'Blog', 'prefix' => 'blogs'],
        'press' => ['label' => 'Press release', 'prefix' => 'media/press'],
        'announcement' => ['label' => 'Announcement', 'prefix' => 'media/announcements'],
        'story' => ['label' => 'Impact story', 'prefix' => 'impact/stories'],
    ];

    protected $fillable = [
        'type', 'category_id', 'post_author_id', 'title', 'slug', 'excerpt', 'body', 'external_url', 'document_id', 'image_id', 'is_featured',
        'hidden_blocks',
        'meta_title', 'meta_description', 'og_image_id', 'canonical_url', 'noindex',
        'status', 'review_state', 'published_at', 'scheduled_for',
    ];

    protected function casts(): array
    {
        return ['hidden_blocks' => 'array', 'is_featured' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            $post->reading_minutes = HtmlSanitizer::readingMinutes($post->body);
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'post_author_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'document_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public static function prefixFor(string $type): string
    {
        return self::Types[$type]['prefix'] ?? 'news';
    }

    public function publicPath(): ?string
    {
        return '/'.self::prefixFor((string) $this->type).'/'.$this->slug;
    }

    public function externalLink(): ?string
    {
        return $this->document?->documentUrl() ?: ($this->external_url ?: null);
    }

    public function snapshotRelations(): array
    {
        return ['tags' => $this->tags()->pluck('tags.id')->all(), 'sections' => $this->sectionsSnapshot()];
    }

    public function restoreRelations(array $relations): void
    {
        if (array_key_exists('tags', $relations)) {
            $this->tags()->sync($relations['tags']);
        }
        if (array_key_exists('sections', $relations)) {
            $this->replaceSections($relations['sections']);
            $this->syncMediaUsage();
        }
    }

    public function previewRelations(array $relations): void
    {
        $this->setRelation('tags', Tag::query()->whereIn('id', $relations['tags'] ?? [])->get());
        $this->previewSections($relations['sections'] ?? []);
    }

    public function additionalMediaReferences(): array
    {
        return ['sections' => $this->sectionMediaIds()];
    }
}
