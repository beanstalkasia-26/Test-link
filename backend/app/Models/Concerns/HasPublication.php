<?php

namespace App\Models\Concerns;

use App\Enums\ContentStatus;
use App\Models\ContentRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasPublication
{
    use FlushesPublicCache;

    public static function bootHasPublication(): void
    {
        static::creating(function ($model): void {
            $model->created_by ??= auth()->id();
            $model->updated_by ??= auth()->id();
        });

        static::updating(function ($model): void {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model): void {
            $model->revisions()->delete();
        });
    }

    public function initializeHasPublication(): void
    {
        $this->mergeCasts([
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'scheduled_for' => 'datetime',
        ]);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('status'), ContentStatus::Published->value)
            ->where(fn (Builder $query) => $query->whereNull($this->qualifyColumn('published_at'))
                ->orWhere($this->qualifyColumn('published_at'), '<=', now()));
    }

    public function isLive(): bool
    {
        return $this->status === ContentStatus::Published
            && ($this->published_at === null || $this->published_at->lte(now()));
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(ContentRevision::class, 'revisionable')->latest('id');
    }

    public function workingCopy(): MorphOne
    {
        return $this->morphOne(ContentRevision::class, 'revisionable')->where('kind', ContentRevision::KindWorking);
    }

    public function revisionableAttributes(): array
    {
        return array_values(array_diff($this->getFillable(), ['status', 'review_state', 'published_at', 'scheduled_for']));
    }

    public function snapshotRelations(): array
    {
        return [];
    }

    public function restoreRelations(array $relations): void {}

    public function previewRelations(array $relations): void {}

    public function toSnapshot(): array
    {
        $attributes = [];
        foreach ($this->revisionableAttributes() as $attribute) {
            $value = $this->getAttribute($attribute);
            $attributes[$attribute] = $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s') : $value;
        }

        return ['attributes' => $attributes, 'relations' => $this->snapshotRelations()];
    }

    abstract public function publicPath(): ?string;

    public function displayTitle(): string
    {
        $attributes = $this->getAttributes();

        return (string) ($attributes['title'] ?? $attributes['name'] ?? '#'.$this->getKey());
    }
}
