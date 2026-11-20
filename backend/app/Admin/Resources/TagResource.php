<?php

namespace App\Admin\Resources;

use App\Enums\Permission;
use App\Models\Tag;

class TagResource extends Resource
{
    public function model(): string
    {
        return Tag::class;
    }

    public function slug(): string
    {
        return 'tags';
    }

    public function label(): string
    {
        return 'Tags';
    }

    public function singular(): string
    {
        return 'Tag';
    }

    public function titleAttribute(): string
    {
        return 'name';
    }

    public function navigationGroup(): string
    {
        return 'Editorial';
    }

    public function permission(): string
    {
        return Permission::ContentEdit;
    }

    public function fields(): array
    {
        return [
            ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => true, 'max' => 80],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'name'],
        ];
    }
}
