<?php

namespace App\Models;

use App\Models\Concerns\CreatesSlugRedirects;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\TracksMediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gallery extends Model
{
    use CreatesSlugRedirects, HasPublication, HasSeo, TracksMediaUsage;

    protected $fillable = [
        'title', 'slug', 'description', 'virtual_tour_url', 'cover_id', 'sort_order',
        'meta_title', 'meta_description', 'og_image_id', 'canonical_url', 'noindex',
        'status', 'review_state', 'published_at', 'scheduled_for',
    ];

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort_order');
    }

    public function publicPath(): ?string
    {
        return '/campus/galleries/'.$this->slug;
    }

    public function snapshotRelations(): array
    {
        return ['items' => $this->items()->get(['media_id', 'type', 'video_url', 'caption'])->toArray()];
    }

    public function restoreRelations(array $relations): void
    {
        if (! array_key_exists('items', $relations)) {
            return;
        }

        $this->items()->delete();
        foreach (array_values($relations['items']) as $index => $item) {
            $this->items()->create([
                'media_id' => $item['media_id'] ?? null,
                'type' => $item['type'] ?? 'image',
                'video_url' => $item['video_url'] ?? null,
                'caption' => $item['caption'] ?? null,
                'sort_order' => $index,
            ]);
        }
        $this->syncMediaUsage();
    }

    public function previewRelations(array $relations): void
    {
        $media = Media::query()->whereIn('id', collect($relations['items'] ?? [])->pluck('media_id')->filter())->get()->keyBy('id');
        $this->setRelation('items', collect($relations['items'] ?? [])->values()->map(
            fn (array $item, int $index) => (new GalleryItem)->forceFill($item + ['id' => -($index + 1), 'sort_order' => $index])
                ->setRelation('media', $media[$item['media_id'] ?? 0] ?? null)
        ));
    }

    public function additionalMediaReferences(): array
    {
        return ['items' => $this->items()->whereNotNull('media_id')->pluck('media_id')->all()];
    }
}
