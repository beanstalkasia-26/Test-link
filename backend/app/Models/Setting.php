<?php

namespace App\Models;

use App\Models\Concerns\FlushesPublicCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use FlushesPublicCache;

    protected $fillable = ['group', 'key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    public static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings:all'));
        static::deleted(fn () => Cache::forget('settings:all'));
    }

    public static function allValues(): array
    {
        return Cache::rememberForever('settings:all', fn (): array => static::query()->pluck('value', 'key')->all());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::allValues()[$key] ?? $default;
    }

    public static function put(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['group' => $group, 'value' => $value]);
    }
}
