<?php

namespace App\Admin\Resources;

use App\Models\Program;
use App\Support\PageBlocks;
use App\Support\ProgramDetails;
use Illuminate\Database\Eloquent\Model;

class ProgramResource extends Resource
{
    public function model(): string
    {
        return Program::class;
    }

    public function slug(): string
    {
        return 'programs';
    }

    public function label(): string
    {
        return 'Programs';
    }

    public function singular(): string
    {
        return 'Program';
    }

    public function titleAttribute(): string
    {
        return 'title';
    }

    public function navigationGroup(): string
    {
        return 'Academics';
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Program title', 'required' => true, 'max' => 200],
            ['name' => 'slug', 'type' => 'slug', 'label' => 'URL slug', 'from' => 'title', 'help' => 'Leave blank to generate from the title. Changing it on a published program creates a redirect.'],
            ['name' => 'level', 'type' => 'select', 'label' => 'Level', 'required' => true, 'options' => Program::Levels],
            ['name' => 'award', 'type' => 'text', 'label' => 'Award / degree', 'max' => 120, 'help' => 'e.g. B.Tech, M.Tech, Master'],
            ['name' => 'school_id', 'type' => 'belongsTo', 'source' => 'school_ids', 'label' => 'School'],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Summary (hero text)', 'max' => 600],
            ['name' => 'body', 'type' => 'richtext', 'label' => 'Program overview'],
            ['name' => 'image_id', 'type' => 'image', 'label' => 'Image'],
            ['name' => 'mode', 'type' => 'text', 'label' => 'Mode', 'max' => 80, 'group' => 'Key facts', 'help' => 'e.g. On-Campus'],
            ['name' => 'location', 'type' => 'text', 'label' => 'Location', 'max' => 80, 'group' => 'Key facts'],
            ['name' => 'intake', 'type' => 'text', 'label' => 'Intake', 'max' => 80, 'group' => 'Key facts', 'help' => 'e.g. August 2026'],
            ['name' => 'application_deadline', 'type' => 'date', 'label' => 'Application deadline', 'group' => 'Key facts'],
            ['name' => 'duration', 'type' => 'text', 'label' => 'Duration', 'max' => 40, 'group' => 'Key facts', 'help' => 'e.g. 2 years'],
            ['name' => 'duration_detail', 'type' => 'text', 'label' => 'Duration detail', 'max' => 120, 'group' => 'Key facts', 'help' => 'e.g. 4 Semesters'],
            ['name' => 'apply_url', 'type' => 'url', 'label' => 'Application link', 'group' => 'Key facts', 'help' => 'External application portal (configurable).'],
            ['name' => 'is_featured', 'type' => 'boolean', 'label' => 'Feature on homepage', 'group' => 'Key facts'],
            ['name' => 'sort_order', 'type' => 'number', 'label' => 'Sort order', 'group' => 'Key facts'],
            ...ProgramDetails::fields(),
            ['name' => 'sections', 'type' => 'sections', 'label' => 'Extra sections', 'group' => 'Sections', 'help' => 'Optional. Shown below the program tabs.'],
            PageBlocks::field(Program::class, 'A block also stays hidden while it has no content.'),
        ];
    }

    public function searchable(): array
    {
        return ['title', 'award', 'summary'];
    }

    public function with(): array
    {
        return ['workingCopy', 'school:id,name'];
    }

    public function columns(): array
    {
        return [['key' => 'title', 'label' => 'Title'], ['key' => 'school', 'label' => 'School'], ['key' => 'level', 'label' => 'Level']];
    }

    public function row(Model $record): array
    {
        return parent::row($record) + ['school' => $record->school?->name, 'level' => $record->levelLabel()];
    }
}
