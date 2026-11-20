<?php

namespace App\Admin\Resources;

use App\Models\Faculty;
use App\Support\PageBlocks;
use Illuminate\Database\Eloquent\Model;

class FacultyResource extends Resource
{
    public function model(): string
    {
        return Faculty::class;
    }

    public function slug(): string
    {
        return 'faculty';
    }

    public function label(): string
    {
        return 'Faculty';
    }

    public function singular(): string
    {
        return 'Faculty profile';
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
            ['name' => 'designation', 'type' => 'text', 'label' => 'Designation', 'max' => 200, 'help' => 'e.g. Dean – Academics'],
            ['name' => 'department', 'type' => 'text', 'label' => 'Department', 'max' => 200],
            ['name' => 'school_id', 'type' => 'belongsTo', 'source' => 'school_ids', 'label' => 'School'],
            ['name' => 'category', 'type' => 'select', 'label' => 'Faculty type', 'required' => true, 'options' => Faculty::Categories],
            ['name' => 'programs', 'type' => 'belongsToMany', 'source' => 'program_ids', 'label' => 'Programs taught'],
            ['name' => 'photo_id', 'type' => 'image', 'label' => 'Portrait photo'],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Short introduction', 'max' => 500],
            ['name' => 'bio', 'type' => 'richtext', 'label' => 'Biography'],
            ['name' => 'qualifications', 'type' => 'repeater', 'label' => 'Education', 'maxItems' => 20, 'fields' => [
                ['name' => 'period', 'type' => 'text', 'label' => 'Period', 'max' => 40],
                ['name' => 'title', 'type' => 'text', 'label' => 'Qualification / role', 'max' => 200, 'required' => true],
                ['name' => 'institution', 'type' => 'text', 'label' => 'Institution', 'max' => 200],
                ['name' => 'text', 'type' => 'textarea', 'label' => 'Description', 'max' => 800],
            ]],
            ['name' => 'experience', 'type' => 'repeater', 'label' => 'Experience', 'maxItems' => 20, 'group' => 'Profile', 'fields' => [
                ['name' => 'period', 'type' => 'text', 'label' => 'Period', 'max' => 40],
                ['name' => 'title', 'type' => 'text', 'label' => 'Role', 'max' => 200, 'required' => true],
                ['name' => 'institution', 'type' => 'text', 'label' => 'Organisation', 'max' => 200],
                ['name' => 'text', 'type' => 'textarea', 'label' => 'Description', 'max' => 800],
            ]],
            ['name' => 'research', 'type' => 'richtext', 'label' => 'Research', 'group' => 'Profile'],
            ['name' => 'publications', 'type' => 'richtext', 'label' => 'Publications', 'group' => 'Profile'],
            ['name' => 'awards', 'type' => 'repeater', 'label' => 'Awards', 'maxItems' => 30, 'group' => 'Profile', 'fields' => [
                ['name' => 'year', 'type' => 'text', 'label' => 'Year', 'max' => 10],
                ['name' => 'title', 'type' => 'text', 'label' => 'Award', 'max' => 200, 'required' => true],
                ['name' => 'text', 'type' => 'textarea', 'label' => 'Description', 'max' => 500],
            ]],
            ['name' => 'research_interests', 'type' => 'textarea', 'label' => 'Research interests', 'max' => 1000],
            ['name' => 'email', 'type' => 'email', 'label' => 'Public email', 'group' => 'Contact'],
            ['name' => 'phone', 'type' => 'text', 'label' => 'Public phone', 'max' => 40, 'group' => 'Contact'],
            ['name' => 'profile_url', 'type' => 'url', 'label' => 'External profile (LinkedIn, Scholar…)', 'group' => 'Contact'],
            ['name' => 'is_featured', 'type' => 'boolean', 'label' => 'Feature on homepage', 'group' => 'Contact'],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order', 'group' => 'Contact'],
            PageBlocks::field(Faculty::class),
        ];
    }

    public function searchable(): array
    {
        return ['name', 'designation', 'department'];
    }

    public function with(): array
    {
        return ['workingCopy'];
    }

    public function columns(): array
    {
        return [['key' => 'title', 'label' => 'Name'], ['key' => 'designation', 'label' => 'Designation']];
    }

    public function row(Model $record): array
    {
        return parent::row($record) + ['designation' => $record->designation];
    }
}
