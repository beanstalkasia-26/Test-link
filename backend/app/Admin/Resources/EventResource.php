<?php

namespace App\Admin\Resources;

use App\Models\Event;
use App\Support\PageBlocks;
use Illuminate\Database\Eloquent\Model;

class EventResource extends Resource
{
    public function model(): string
    {
        return Event::class;
    }

    public function slug(): string
    {
        return 'events';
    }

    public function label(): string
    {
        return 'Events';
    }

    public function singular(): string
    {
        return 'Event';
    }

    public function navigationGroup(): string
    {
        return 'Editorial';
    }

    public function defaultSort(): array
    {
        return ['starts_at', 'desc'];
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Event title', 'required' => true, 'max' => 200],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'title'],
            ['name' => 'category', 'type' => 'select', 'label' => 'Type', 'required' => true, 'options' => Event::Categories],
            ['name' => 'subtitle', 'type' => 'text', 'label' => 'Subtitle', 'max' => 200, 'help' => 'e.g. Masterclass by Prof. Daniel Philip, MIT'],
            ['name' => 'school_id', 'type' => 'belongsTo', 'source' => 'school_ids', 'label' => 'School'],
            ['name' => 'centre_id', 'type' => 'belongsTo', 'source' => 'centre_ids', 'label' => 'Centre'],
            ['name' => 'starts_at', 'type' => 'datetime', 'label' => 'Starts', 'required' => true],
            ['name' => 'ends_at', 'type' => 'datetime', 'label' => 'Ends'],
            ['name' => 'venue', 'type' => 'text', 'label' => 'Venue', 'max' => 200],
            ['name' => 'registration_url', 'type' => 'url', 'label' => 'Registration link'],
            ['name' => 'image_id', 'type' => 'image', 'label' => 'Image'],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Summary', 'max' => 400],
            ['name' => 'overview', 'type' => 'richtext', 'label' => 'Event overview', 'group' => 'Details'],
            ['name' => 'outcomes', 'type' => 'repeater', 'label' => "What you'll learn", 'maxItems' => 12, 'group' => 'Details', 'fields' => [
                ['name' => 'text', 'type' => 'textarea', 'label' => 'Point', 'max' => 400, 'required' => true],
            ]],
            ['name' => 'speaker', 'type' => 'group', 'label' => 'Speaker', 'group' => 'Speaker', 'fields' => [
                ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'max' => 120],
                ['name' => 'role', 'type' => 'text', 'label' => 'Role', 'max' => 200],
                ['name' => 'photo', 'type' => 'image', 'label' => 'Photo'],
                ['name' => 'quote', 'type' => 'textarea', 'label' => 'Quote', 'max' => 500],
                ['name' => 'bio', 'type' => 'richtext', 'label' => 'About the speaker'],
            ]],
            PageBlocks::field(Event::class),
        ];
    }

    public function with(): array
    {
        return ['workingCopy'];
    }

    public function columns(): array
    {
        return [['key' => 'title', 'label' => 'Title'], ['key' => 'category', 'label' => 'Type'], ['key' => 'date', 'label' => 'Date']];
    }

    public function row(Model $record): array
    {
        return parent::row($record) + ['category' => Event::Categories[$record->category] ?? $record->category, 'date' => $record->starts_at?->format('d M Y')];
    }
}
