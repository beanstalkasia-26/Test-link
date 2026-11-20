<?php

namespace App\Models\Concerns;

use App\Support\ContentCache;

trait FlushesPublicCache
{
    public static function bootFlushesPublicCache(): void
    {
        static::saved(fn () => ContentCache::flush());
        static::deleted(fn () => ContentCache::flush());
    }
}
