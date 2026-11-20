<?php

namespace App\Admin\Resources;

use App\Enums\Permission;
use App\Models\Initiative;
use App\Sections\SectionRegistry;

class InitiativeResource extends Resource
{
    public function model(): string
    {
        return Initiative::class;
    }

    public function slug(): string
    {
        return 'initiatives';
    }

    public function label(): string
    {
        return 'Mission initiatives';
    }

    public function singular(): string
    {
        return 'Initiative';
    }

    public function titleAttribute(): string
    {
        return 'title';
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
            ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'required' => true, 'max' => 120],
            ['name' => 'body', 'type' => 'textarea', 'label' => 'Text', 'required' => true, 'max' => 500],
            ['name' => 'icon', 'type' => 'select', 'source' => 'icons', 'label' => 'Icon', 'options' => SectionRegistry::icons()],
            ['name' => 'is_active', 'type' => 'boolean', 'label' => 'Show on site'],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order'],
        ];
    }
}
