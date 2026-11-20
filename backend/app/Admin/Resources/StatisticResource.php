<?php

namespace App\Admin\Resources;

use App\Enums\Permission;
use App\Models\Statistic;

class StatisticResource extends Resource
{
    public function model(): string
    {
        return Statistic::class;
    }

    public function slug(): string
    {
        return 'statistics';
    }

    public function label(): string
    {
        return 'Approved statistics';
    }

    public function singular(): string
    {
        return 'Statistic';
    }

    public function titleAttribute(): string
    {
        return 'label';
    }

    public function navigationGroup(): string
    {
        return 'Mission';
    }

    public function permission(): string
    {
        return Permission::ContentPublish;
    }

    public function fields(): array
    {
        return [
            ['name' => 'group', 'type' => 'text', 'label' => 'Group', 'required' => true, 'max' => 60, 'help' => 'Sections pick a group, e.g. mission, admissions, placements.'],
            ['name' => 'value', 'type' => 'text', 'label' => 'Value', 'required' => true, 'max' => 40, 'help' => 'e.g. 3 million, 93%'],
            ['name' => 'label', 'type' => 'text', 'label' => 'Label', 'required' => true, 'max' => 200],
            ['name' => 'source_note', 'type' => 'text', 'label' => 'Source / evidence (internal)', 'max' => 255],
            ['name' => 'is_approved', 'type' => 'boolean', 'label' => 'Approved for publication', 'help' => 'Only approved statistics appear on the website.'],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order'],
        ];
    }
}
