<?php

namespace App\Admin\Resources;

use App\Models\Gallery;
use Illuminate\Database\Eloquent\Model;

class GalleryResource extends Resource
{
    public function model(): string
    {
        return Gallery::class;
    }

    public function slug(): string
    {
        return 'galleries';
    }

    public function label(): string
    {
        return 'Campus galleries';
    }

    public function singular(): string
    {
        return 'Gallery';
    }

    public function titleAttribute(): string
    {
        return 'title';
    }

    public function navigationGroup(): string
    {
        return 'Campus';
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'required' => true, 'max' => 200],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'title'],
            ['name' => 'description', 'type' => 'textarea', 'label' => 'Description', 'max' => 1000],
            ['name' => 'cover_id', 'type' => 'image', 'label' => 'Cover image'],
            ['name' => 'virtual_tour_url', 'type' => 'url', 'label' => 'Virtual tour link', 'help' => 'Embedded only for approved hosts, and only after the visitor clicks.'],
            ['name' => 'items', 'type' => 'repeater', 'label' => 'Gallery items', 'maxItems' => 200, 'fields' => [
                ['name' => 'media_id', 'type' => 'image', 'label' => 'Image', 'required' => true],
                ['name' => 'caption', 'type' => 'text', 'label' => 'Caption', 'max' => 200],
                ['name' => 'type', 'type' => 'select', 'label' => 'Type', 'options' => ['image' => 'Image', 'video' => 'Video (poster image)']],
                ['name' => 'video_url', 'type' => 'url', 'label' => 'Video link'],
            ]],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order'],
        ];
    }

    public function with(): array
    {
        return ['workingCopy'];
    }

    public function extract(array $validated, ?Model $record): array
    {
        [$attributes, $relations, $media] = parent::extract($validated, $record);
        $relations['items'] = $attributes['items'] ?? [];
        unset($attributes['items']);

        return [$attributes, $relations, $media];
    }
}
