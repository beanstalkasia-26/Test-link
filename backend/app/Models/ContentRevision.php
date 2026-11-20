<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentRevision extends Model
{
    public const KindWorking = 'working';

    public const KindSnapshot = 'snapshot';

    protected $fillable = ['revisionable_type', 'revisionable_id', 'user_id', 'kind', 'note', 'data'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
