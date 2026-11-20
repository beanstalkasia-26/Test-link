<?php

namespace App\Admin\Resources;

use App\Enums\Permission;
use App\Models\PartnershipCategory;

class PartnershipCategoryResource extends Resource
{
    public function model(): string
    {
        return PartnershipCategory::class;
    }

    public function slug(): string
    {
        return 'partnership-categories';
    }

    public function label(): string
    {
        return 'Partnership categories';
    }

    public function singular(): string
    {
        return 'Partnership category';
    }

    public function titleAttribute(): string
    {
        return 'name';
    }

    public function navigationGroup(): string
    {
        return 'Partnerships';
    }

    public function permission(): string
    {
        return Permission::ContentEdit;
    }

    public function fields(): array
    {
        return [
            ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => true, 'max' => 120],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'name'],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order'],
        ];
    }
}
