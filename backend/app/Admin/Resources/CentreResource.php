<?php

namespace App\Admin\Resources;

use App\Models\Centre;
use App\Support\PageBlocks;

class CentreResource extends Resource
{
    public function model(): string
    {
        return Centre::class;
    }

    public function slug(): string
    {
        return 'centres';
    }

    public function label(): string
    {
        return 'Centres';
    }

    public function singular(): string
    {
        return 'Centre';
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
            ['name' => 'school_id', 'type' => 'belongsTo', 'source' => 'school_ids', 'label' => 'Parent school (optional)'],
            ['name' => 'tagline', 'type' => 'text', 'label' => 'Tagline', 'max' => 200],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Summary', 'max' => 600],
            ['name' => 'body', 'type' => 'richtext', 'label' => 'About the centre'],
            ['name' => 'image_id', 'type' => 'image', 'label' => 'Image'],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order'],
            ['name' => 'sections', 'type' => 'sections', 'label' => 'Landing page sections', 'group' => 'Sections'],
            PageBlocks::field(Centre::class, 'Not used when the page is built from sections.'),
        ];
    }

    public function with(): array
    {
        return ['workingCopy'];
    }
}
