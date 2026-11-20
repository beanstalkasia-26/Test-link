<?php

namespace App\Models;

use App\Models\Concerns\FlushesPublicCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartnershipCategory extends Model
{
    use FlushesPublicCache;

    protected $fillable = ['name', 'slug', 'sort_order'];

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class)->orderBy('sort_order');
    }

    public function displayTitle(): string
    {
        return $this->name;
    }
}
