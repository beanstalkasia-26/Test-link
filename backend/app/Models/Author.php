<?php

namespace App\Models;

use App\Models\Concerns\FlushesPublicCache;
use App\Models\Concerns\TracksMediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    use FlushesPublicCache, TracksMediaUsage;

    protected $fillable = ['name', 'slug', 'designation', 'bio', 'photo_id'];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'post_author_id');
    }

    public function displayTitle(): string
    {
        return $this->name;
    }
}
