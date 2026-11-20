<?php

namespace App\Admin\Resources;

use App\Enums\Permission;
use App\Models\Category;

class CategoryResource extends Resource
{
    public function model(): string
    {
        return Category::class;
    }

    public function slug(): string
    {
        return 'categories';
    }

    public function label(): string
    {
        return 'Categories';
    }

    public function singular(): string
    {
        return 'Category';
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
            ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => true, 'max' => 120],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'name'],
            ['name' => 'description', 'type' => 'textarea', 'label' => 'Description', 'max' => 500],
        ];
    }
}
