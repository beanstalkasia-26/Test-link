<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\Author;
use App\Models\Category;
use App\Models\Centre;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\Gallery;
use App\Models\JobOpening;
use App\Models\Leader;
use App\Models\Page;
use App\Models\PartnershipCategory;
use App\Models\Post;
use App\Models\Program;
use App\Models\School;
use App\Models\Statistic;
use App\Models\Tag;
use App\Sections\SectionRegistry;

class OptionSources
{
    private static array $cache = [];

    public static function get(string $source): array
    {
        return self::$cache[$source] ??= match ($source) {
            'school_slugs' => School::query()->orderBy('sort_order')->pluck('name', 'slug')->all(),
            'faculty_slugs' => Faculty::query()->orderBy('name')->pluck('name', 'slug')->all(),
            'gallery_slugs' => Gallery::query()->orderBy('title')->pluck('title', 'slug')->all(),
            'partnership_category_slugs' => PartnershipCategory::query()->orderBy('sort_order')->pluck('name', 'slug')->all(),
            'category_slugs' => Category::query()->orderBy('name')->pluck('name', 'slug')->all(),
            'statistic_groups' => Statistic::query()->distinct()->orderBy('group')->pluck('group', 'group')->all(),
            'post_types' => collect(Post::Types)->map(fn (array $type) => $type['label'])->all(),
            'post_types_required' => collect(Post::Types)->map(fn (array $type) => $type['label'])->all(),
            'program_levels' => Program::Levels,
            'school_ids' => School::query()->orderBy('sort_order')->pluck('name', 'id')->all(),
            'centre_ids' => Centre::query()->orderBy('sort_order')->pluck('name', 'id')->all(),
            'leader_groups' => Leader::Groups,
            'event_categories' => Event::Categories,
            'job_categories' => JobOpening::Categories,
            'faculty_categories' => Faculty::Categories,
            'program_ids' => Program::query()->orderBy('title')->pluck('title', 'id')->all(),
            'category_ids' => Category::query()->orderBy('name')->pluck('name', 'id')->all(),
            'tag_ids' => Tag::query()->orderBy('name')->pluck('name', 'id')->all(),
            'author_ids' => Author::query()->orderBy('name')->pluck('name', 'id')->all(),
            'partnership_category_ids' => PartnershipCategory::query()->orderBy('sort_order')->pluck('name', 'id')->all(),
            'page_ids' => Page::query()->orderBy('path')->get(['id', 'title', 'path'])->mapWithKeys(fn (Page $p) => [$p->id => $p->title.' (/'.$p->path.')'])->all(),
            'roles' => collect(Role::cases())->mapWithKeys(fn (Role $role) => [$role->value => $role->label()])->all(),
            'icons' => SectionRegistry::icons(),
            default => [],
        };
    }

    public static function forget(): void
    {
        self::$cache = [];
    }
}
