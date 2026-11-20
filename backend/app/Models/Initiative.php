<?php

namespace App\Models;

use App\Models\Concerns\FlushesPublicCache;
use Illuminate\Database\Eloquent\Model;

class Initiative extends Model
{
    use FlushesPublicCache;

    protected $fillable = ['title', 'body', 'icon', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function displayTitle(): string
    {
        return $this->title;
    }
}
