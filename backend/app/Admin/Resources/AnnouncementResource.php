<?php

namespace App\Admin\Resources;

use App\Enums\Permission;
use App\Models\Announcement;

class AnnouncementResource extends Resource
{
    public function model(): string
    {
        return Announcement::class;
    }

    public function slug(): string
    {
        return 'announcements';
    }

    public function label(): string
    {
        return 'Announcement strip';
    }

    public function singular(): string
    {
        return 'Announcement';
    }

    public function titleAttribute(): string
    {
        return 'text';
    }

    public function navigationGroup(): string
    {
        return 'Website';
    }

    public function permission(): string
    {
        return Permission::ContentPublish;
    }

    public function fields(): array
    {
        return [
            ['name' => 'label', 'type' => 'text', 'label' => 'Badge', 'max' => 30, 'help' => 'e.g. New'],
            ['name' => 'text', 'type' => 'text', 'label' => 'Announcement text', 'required' => true, 'max' => 200],
            ['name' => 'url', 'type' => 'url', 'label' => 'Link'],
            ['name' => 'is_active', 'type' => 'boolean', 'label' => 'Active'],
            ['name' => 'starts_at', 'type' => 'datetime', 'label' => 'Show from'],
            ['name' => 'ends_at', 'type' => 'datetime', 'label' => 'Show until'],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order'],
        ];
    }
}
