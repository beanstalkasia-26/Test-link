<?php

namespace App\Admin\Resources;

use App\Models\JobOpening;
use Illuminate\Database\Eloquent\Model;

class JobOpeningResource extends Resource
{
    public function model(): string
    {
        return JobOpening::class;
    }

    public function slug(): string
    {
        return 'jobs';
    }

    public function label(): string
    {
        return 'Job openings';
    }

    public function singular(): string
    {
        return 'Job opening';
    }

    public function navigationGroup(): string
    {
        return 'Careers';
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Job title', 'required' => true, 'max' => 200],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'title'],
            ['name' => 'category', 'type' => 'select', 'label' => 'Category', 'required' => true, 'options' => JobOpening::Categories],
            ['name' => 'department', 'type' => 'text', 'label' => 'School / department', 'max' => 200],
            ['name' => 'closes_on', 'type' => 'date', 'label' => 'Last date to apply'],
            ['name' => 'mode', 'type' => 'text', 'label' => 'Mode / location', 'max' => 120, 'help' => 'e.g. On Campus (Gujarat Campus), Remote'],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Summary', 'max' => 500],
            ['name' => 'description', 'type' => 'richtext', 'label' => 'Job description'],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order'],
        ];
    }

    public function with(): array
    {
        return ['workingCopy'];
    }

    public function columns(): array
    {
        return [['key' => 'title', 'label' => 'Title'], ['key' => 'category', 'label' => 'Category'], ['key' => 'closes', 'label' => 'Closes']];
    }

    public function row(Model $record): array
    {
        return parent::row($record) + ['category' => JobOpening::Categories[$record->category] ?? $record->category, 'closes' => $record->closes_on?->format('d M Y')];
    }
}
