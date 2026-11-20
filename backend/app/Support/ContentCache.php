<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class ContentCache
{
    private const VersionKey = 'public-content-version';

    public static function version(): int
    {
        return (int) Cache::rememberForever(self::VersionKey, fn (): int => 1);
    }

    public static function flush(): void
    {
        Cache::forever(self::VersionKey, self::version() + 1);
    }

    public static function key(string $key): string
    {
        return 'public:v'.self::version().':'.$key;
    }

    public static function remember(string $key, callable $callback, int $seconds = 3600): mixed
    {
        return Cache::remember(self::key($key), $seconds, $callback);
    }
}
