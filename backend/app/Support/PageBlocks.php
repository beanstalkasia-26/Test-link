<?php

namespace App\Support;

use App\Models\Centre;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\Leader;
use App\Models\Post;
use App\Models\Program;
use App\Models\School;
use Illuminate\Database\Eloquent\Model;

class PageBlocks
{
    private const Blocks = [
        School::class => [
            'hero_image' => 'Hero image',
            'about' => 'About the school',
            'programs' => 'Programs',
            'centres' => 'Centres',
            'faculty' => 'Faculty',
        ],
        Centre::class => [
            'hero_image' => 'Hero image',
            'about' => 'About the centre',
        ],
        Faculty::class => [
            'contact' => 'Email and profile buttons',
            'biography' => 'Biography',
            'details' => 'Education, experience, research and awards',
            'programs' => 'Teaches on',
        ],
        Post::class => [
            'hero_image' => 'Hero image',
            'contents' => 'Table of contents',
            'latest' => 'Latest blogs',
            'tags' => 'Tags',
            'related' => 'Related articles',
        ],
        Event::class => [
            'hero_image' => 'Event image',
            'overview' => 'Overview',
            'outcomes' => 'What you’ll learn',
            'speaker' => 'Speaker',
            'registration' => 'Registration banner',
            'related' => 'Other events',
        ],
        Leader::class => [
            'biography' => 'Biography',
            'achievements' => 'Awards & achievements',
        ],
    ];

    public static function for(string $model): array
    {
        return $model === Program::class ? ProgramDetails::blockLabels() : (self::Blocks[$model] ?? []);
    }

    public static function field(string $model, string $help = ''): array
    {
        return [
            'name' => 'hidden_blocks',
            'type' => 'blocks',
            'label' => 'Blocks shown on this page',
            'options' => self::for($model),
            'group' => 'Page blocks',
            'help' => trim('Switch a block off to hide it from the public page. '.$help),
        ];
    }

    public static function hidden(Model $record): array
    {
        return array_values(array_intersect(array_keys(self::for($record::class)), (array) ($record->getAttribute('hidden_blocks') ?? [])));
    }
}
