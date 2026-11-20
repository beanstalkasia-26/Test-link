<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageSection extends Model
{
    protected $fillable = ['sectionable_type', 'sectionable_id', 'type', 'label', 'anchor', 'is_visible', 'sort_order', 'data'];

    protected function casts(): array
    {
        return ['data' => 'array', 'is_visible' => 'boolean'];
    }

    public function sectionable(): MorphTo
    {
        return $this->morphTo();
    }
}
