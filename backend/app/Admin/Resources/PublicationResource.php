<?php

namespace App\Admin\Resources;

use App\Models\Publication;

class PublicationResource extends Resource
{
    public function model(): string
    {
        return Publication::class;
    }

    public function slug(): string
    {
        return 'publications';
    }

    public function label(): string
    {
        return 'Research publications';
    }

    public function singular(): string
    {
        return 'Publication';
    }

    public function navigationGroup(): string
    {
        return 'Editorial';
    }

    public function defaultSort(): array
    {
        return ['published_on', 'desc'];
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'required' => true, 'max' => 500],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'title'],
            ['name' => 'authors', 'type' => 'text', 'label' => 'Authors', 'max' => 255],
            ['name' => 'publisher', 'type' => 'text', 'label' => 'Journal / publisher', 'max' => 255],
            ['name' => 'published_on', 'type' => 'date', 'label' => 'Publication date'],
            ['name' => 'school_id', 'type' => 'belongsTo', 'source' => 'school_ids', 'label' => 'School'],
            ['name' => 'topic', 'type' => 'text', 'label' => 'Topic', 'max' => 100],
            ['name' => 'image_id', 'type' => 'image', 'label' => 'Image'],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Summary', 'max' => 600],
            ['name' => 'body', 'type' => 'richtext', 'label' => 'Abstract / details'],
            ['name' => 'url', 'type' => 'url', 'label' => 'Link to full paper'],
        ];
    }

    public function with(): array
    {
        return ['workingCopy'];
    }
}
