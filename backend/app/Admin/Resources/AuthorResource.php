<?php

namespace App\Admin\Resources;

use App\Enums\Permission;
use App\Models\Author;

class AuthorResource extends Resource
{
    public function model(): string
    {
        return Author::class;
    }

    public function slug(): string
    {
        return 'authors';
    }

    public function label(): string
    {
        return 'Authors';
    }

    public function singular(): string
    {
        return 'Author';
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
            ['name' => 'designation', 'type' => 'text', 'label' => 'Designation', 'max' => 160],
            ['name' => 'bio', 'type' => 'textarea', 'label' => 'Short bio', 'max' => 1000],
            ['name' => 'photo_id', 'type' => 'image', 'label' => 'Photo'],
        ];
    }
}
