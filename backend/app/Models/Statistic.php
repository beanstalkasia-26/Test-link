<?php

namespace App\Models;

use App\Models\Concerns\FlushesPublicCache;
use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    use FlushesPublicCache;

    protected $fillable = ['group', 'value', 'label', 'source_note', 'is_approved', 'sort_order'];

    protected function casts(): array
    {
        return ['is_approved' => 'boolean'];
    }

    public static function approvedFor(?string $group): array
    {
        if (! $group) {
            return [];
        }

        return static::query()->where('group', $group)->where('is_approved', true)->orderBy('sort_order')
            ->get(['value', 'label'])->toArray();
    }

    public function displayTitle(): string
    {
        return $this->value.' '.$this->label;
    }
}
