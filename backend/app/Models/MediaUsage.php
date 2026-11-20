<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaUsage extends Model
{
    protected $fillable = ['media_id', 'usable_type', 'usable_id', 'field'];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function usable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function syncFor(Model $usable, array $references): void
    {
        static::query()
            ->where('usable_type', $usable->getMorphClass())
            ->where('usable_id', $usable->getKey())
            ->delete();

        $existing = Media::query()->whereIn('id', collect($references)->flatten()->unique()->all())->pluck('id')->all();
        $rows = [];
        foreach ($references as $field => $ids) {
            foreach (array_unique($ids) as $id) {
                if (in_array($id, $existing, true)) {
                    $rows[] = [
                        'media_id' => $id,
                        'usable_type' => $usable->getMorphClass(),
                        'usable_id' => $usable->getKey(),
                        'field' => substr($field, 0, 100),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if ($rows !== []) {
            static::query()->insertOrIgnore($rows);
        }
    }
}
