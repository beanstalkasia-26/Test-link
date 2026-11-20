<?php

namespace App\Admin\Resources;

use App\Models\Partner;

class PartnerResource extends Resource
{
    public function model(): string
    {
        return Partner::class;
    }

    public function slug(): string
    {
        return 'partners';
    }

    public function label(): string
    {
        return 'Partners';
    }

    public function singular(): string
    {
        return 'Partner';
    }

    public function titleAttribute(): string
    {
        return 'name';
    }

    public function navigationGroup(): string
    {
        return 'Partnerships';
    }

    public function fields(): array
    {
        return [
            ['name' => 'name', 'type' => 'text', 'label' => 'Partner name', 'required' => true, 'max' => 200],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'name'],
            ['name' => 'partnership_category_id', 'type' => 'belongsTo', 'source' => 'partnership_category_ids', 'label' => 'Category'],
            ['name' => 'logo_id', 'type' => 'image', 'label' => 'Logo', 'help' => 'Use the partner-approved logo file.'],
            ['name' => 'image_id', 'type' => 'image', 'label' => 'Story image', 'help' => 'Also the photo on program pages (Program Partners).'],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Summary', 'max' => 400, 'help' => 'Shown on program partner cards (up to 3 lines).'],
            ['name' => 'highlight', 'type' => 'text', 'label' => 'Highlight line', 'max' => 120, 'help' => 'Only use figures confirmed by the partner.'],
            ['name' => 'story', 'type' => 'richtext', 'label' => 'Partnership story', 'help' => 'Adds a "Read story" page when filled in.'],
            ['name' => 'website_url', 'type' => 'url', 'label' => 'Partner website'],
            ['name' => 'is_featured', 'type' => 'boolean', 'label' => 'Featured', 'help' => 'Shown in the stories carousel and, with a story image, as a partner on programs without their own partner list.'],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order'],
        ];
    }

    public function with(): array
    {
        return ['workingCopy'];
    }
}
