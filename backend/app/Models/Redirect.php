<?php

namespace App\Models;

use App\Models\Concerns\FlushesPublicCache;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use FlushesPublicCache;

    protected $fillable = ['from_path', 'to_url', 'status_code', 'is_automatic'];

    protected function casts(): array
    {
        return ['is_automatic' => 'boolean', 'last_hit_at' => 'datetime'];
    }

    public static function normalizePath(string $path): string
    {
        $path = '/'.trim((string) parse_url($path, PHP_URL_PATH), '/');

        return $path === '' ? '/' : $path;
    }

    public static function record(string $from, string $to): void
    {
        $from = self::normalizePath($from);
        $to = self::normalizePath($to);

        if ($from === $to) {
            return;
        }

        static::query()->where('from_path', $to)->delete();

        static::query()->where('to_url', $from)->update(['to_url' => $to]);

        static::updateOrCreate(['from_path' => $from], ['to_url' => $to, 'status_code' => 301, 'is_automatic' => true]);
    }
}
