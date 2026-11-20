<?php

namespace App\Admin\Resources;

use App\Models\School;
use App\Support\PageBlocks;

class SchoolResource extends Resource
{
    public function model(): string
    {
        return School::class;
    }

    public function slug(): string
    {
        return 'schools';
    }

    public function label(): string
    {
        return 'Schools';
    }

    public function singular(): string
    {
        return 'School';
    }

    public function titleAttribute(): string
    {
        return 'name';
    }

    public function navigationGroup(): string
    {
        return 'Academics';
    }

    public function fields(): array
    {
        return [
            ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => true, 'max' => 200],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'name'],
            ['name' => 'tagline', 'type' => 'text', 'label' => 'Tagline', 'max' => 200],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Summary', 'max' => 600],
            ['name' => 'body', 'type' => 'richtext', 'label' => 'About the school'],
            ['name' => 'image_id', 'type' => 'image', 'label' => 'Image'],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order'],
            ['name' => 'sections', 'type' => 'sections', 'label' => 'Landing page sections', 'group' => 'Sections'],
            PageBlocks::field(School::class, 'Not used when the page is built from sections.'),
        ];
    }

    public function with(): array
    {
        return ['workingCopy'];
    }
}
