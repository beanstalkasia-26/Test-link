<?php

namespace App\Admin\Resources;

use App\Models\Leader;
use App\Support\PageBlocks;
use Illuminate\Database\Eloquent\Model;

class LeaderResource extends Resource
{
    public function model(): string
    {
        return Leader::class;
    }

    public function slug(): string
    {
        return 'leaders';
    }

    public function label(): string
    {
        return 'Leadership & governance';
    }

    public function singular(): string
    {
        return 'Leadership profile';
    }

    public function titleAttribute(): string
    {
        return 'name';
    }

    public function navigationGroup(): string
    {
        return 'People';
    }

    public function fields(): array
    {
        return [
            ['name' => 'name', 'type' => 'text', 'label' => 'Full name', 'required' => true, 'max' => 200],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'name'],
            ['name' => 'group', 'type' => 'select', 'label' => 'Group', 'required' => true, 'options' => Leader::Groups],
            ['name' => 'designation', 'type' => 'text', 'label' => 'Designation', 'max' => 200],
            ['name' => 'organisation', 'type' => 'text', 'label' => 'Organisation', 'max' => 200],
            ['name' => 'photo_id', 'type' => 'image', 'label' => 'Photo'],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Card text', 'max' => 300],
            ['name' => 'bio', 'type' => 'richtext', 'label' => 'Biography'],
            ['name' => 'email', 'type' => 'email', 'label' => 'Public email'],
            ['name' => 'achievements', 'type' => 'repeater', 'label' => 'Awards & achievements', 'maxItems' => 30, 'fields' => [
                ['name' => 'year', 'type' => 'text', 'label' => 'Year', 'max' => 10],
                ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'max' => 200, 'required' => true],
                ['name' => 'text', 'type' => 'textarea', 'label' => 'Description', 'max' => 500],
            ]],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order'],
            PageBlocks::field(Leader::class),
        ];
    }

    public function searchable(): array
    {
        return ['name', 'designation'];
    }

    public function with(): array
    {
        return ['workingCopy'];
    }

    public function columns(): array
    {
        return [['key' => 'title', 'label' => 'Name'], ['key' => 'group', 'label' => 'Group']];
    }

    public function row(Model $record): array
    {
        return parent::row($record) + ['group' => Leader::Groups[$record->group] ?? $record->group];
    }
}
