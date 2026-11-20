<?php

namespace App\Models;

use App\Models\Concerns\FlushesPublicCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use FlushesPublicCache;

    public const Widths = [400, 800, 1200, 1600, 2000];

    public const Format = 'webp';

    public const NamedSizes = ['sm' => 400, 'md' => 800, 'lg' => 1200, 'xl' => 1600];

    protected $fillable = [
        'uuid', 'kind', 'disk', 'directory', 'filename', 'original_name', 'mime_type', 'size',
        'width', 'height', 'alt', 'caption', 'credit', 'variants', 'status', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'variants' => 'array',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function availableWidths(): array
    {
        return array_map('intval', $this->variants['widths'] ?? []);
    }

    public function variantPath(int $width, string $format): string
    {
        return $this->directory.'/'.$width.'.'.$format;
    }

    public function url(string|int $size = 'lg'): ?string
    {
        $widths = $this->availableWidths();
        if ($widths === []) {
            return null;
        }

        $target = is_int($size) ? $size : (self::NamedSizes[$size] ?? 1200);
        $chosen = collect($widths)->sortBy(fn (int $w) => abs($w - $target))->first();

        return Storage::disk('public')->url($this->variantPath($chosen, self::Format));
    }

    public function toImage(?string $altOverride = null): ?array
    {
        if ($this->status !== 'ready' || $this->availableWidths() === []) {
            return null;
        }

        $disk = Storage::disk('public');
        $srcset = collect($this->availableWidths())
            ->map(fn (int $w): string => $disk->url($this->variantPath($w, self::Format)).' '.$w.'w')
            ->implode(', ');

        return [
            'id' => $this->id,
            'src' => $this->url('lg'),
            'srcset' => $srcset,
            'width' => $this->width,
            'height' => $this->height,
            'alt' => $altOverride ?? (string) $this->alt,
            'caption' => $this->caption,
        ];
    }

    public function isDocument(): bool
    {
        return $this->kind === 'document';
    }

    public function documentUrl(): ?string
    {
        return $this->isDocument() ? Storage::disk('public')->url($this->directory.'/'.basename($this->filename)) : null;
    }

    public function toAdmin(): array
    {
        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'document_url' => $this->documentUrl(),
            'thumb' => $this->url('sm'),
            'url' => $this->url('lg'),
            'original_name' => $this->original_name,
            'alt' => $this->alt,
            'caption' => $this->caption,
            'credit' => $this->credit,
            'width' => $this->width,
            'height' => $this->height,
            'size' => $this->size,
            'mime_type' => $this->mime_type,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
