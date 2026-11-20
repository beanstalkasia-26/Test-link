<?php

namespace App\Sections;

use App\Models\Media;
use App\Models\Program;

class SampleContent
{
    public const Text = 'SAMPLE – replace with real content.';

    private array|null|false $image = false;

    public function fill(string $type, array $fields, array $data): array
    {
        $data = $this->fillRepeaters($fields, $data);
        $empty = fn (string $key): bool => empty($data[$key]);

        return match ($type) {
            'events_carousel' => $empty('events') ? [...$data, 'events' => $this->many(fn (int $i) => $this->event($i))] : $data,
            'news_carousel', 'post_list' => $empty('posts') ? [...$data, 'posts' => $this->many(fn (int $i) => $this->post($i), 4)] : $data,
            'people', 'faculty_showcase' => $empty('people') ? [...$data, 'people' => $this->many(fn (int $i) => $this->person($i), 4)] : $data,
            'leaders_grid' => $empty('people') ? [...$data, 'people' => $this->many(fn (int $i) => $this->leader($i), 4)] : $data,
            'programs_list' => $empty('programs') ? [...$data, 'programs' => $this->many(fn (int $i) => $this->program($i))] : $data,
            'program_finder' => $empty('programs') ? [...$data, 'programs' => $this->many(fn (int $i) => $this->program($i) + ['school_id' => null])] : $data,
            'programs_tabs' => $empty('levels') ? [...$data, 'levels' => [['key' => 'sample', 'label' => 'SAMPLE – Programs', 'programs' => $this->many(fn (int $i) => $this->program($i))]]] : $data,
            'logo_strip' => $empty('logos') ? [...$data, 'logos' => $this->many(fn (int $i) => $this->logo($i), 6)] : $data,
            'partners_showcase' => $this->partners($data),
            'publications_carousel' => $empty('publications') ? [...$data, 'publications' => $this->many(fn (int $i) => $this->publication($i), 4)] : $data,
            'stats_band' => $empty('statistics') ? [...$data, 'statistics' => $this->many(fn (int $i) => ['value' => 'SAMPLE', 'label' => 'SAMPLE – figure '.$i], 3)] : $data,
            'gallery', 'campus_showcase' => $this->gallery($data),
            default => $data,
        };
    }

    private function fillRepeaters(array $fields, array $data): array
    {
        foreach ($fields as $field) {
            if ($field['type'] === 'repeater' && ($field['minItems'] ?? 0) >= 1 && empty($data[$field['name']])) {
                $data[$field['name']] = $this->many(fn (int $i) => $this->item($field['fields'], $i), max(2, $field['minItems']));
            }
        }

        return $data;
    }

    public function item(array $fields, int $i): array
    {
        $item = [];
        foreach ($fields as $field) {
            $item[$field['name']] = match ($field['type']) {
                'text' => "SAMPLE – {$field['label']} {$i}",
                'textarea' => self::Text,
                'richtext' => '<p>'.self::Text.'</p>',
                'image' => $this->image(),
                'number' => 10 * $i,
                'select' => ($field['name'] === 'icon') ? 'sparkle' : array_key_first($field['options'] ?? []),
                'repeater' => ($field['minItems'] ?? 0) >= 1 ? $this->many(fn (int $j) => $this->item($field['fields'], $j), 2) : [],
                default => null,
            };
        }

        return $item;
    }

    private function partners(array $data): array
    {
        if (empty($data['logos']) && empty($data['hide_logos'])) {
            $data['logos'] = $this->many(fn (int $i) => $this->logo($i), 6);
        }
        if (! empty($data['show_stories']) && empty($data['stories'])) {
            $data['stories'] = $this->many(fn (int $i) => ['id' => "sample-{$i}", 'name' => "SAMPLE – Partner {$i}", 'summary' => self::Text, 'highlight' => null, 'url' => null, 'image' => $this->image()], 3);
        }

        return $data;
    }

    private function gallery(array $data): array
    {
        if (! empty($data['gallery']['items'])) {
            return $data;
        }
        $data['gallery'] = [
            ...($data['gallery'] ?? ['id' => null, 'title' => 'SAMPLE – Gallery', 'slug' => null, 'url' => null, 'description' => null, 'virtual_tour_url' => null, 'tour_embeddable' => false, 'cover' => null]),
            'items' => $this->many(fn (int $i) => ['id' => "sample-{$i}", 'type' => 'image', 'video_url' => null, 'caption' => "SAMPLE – Photo {$i}", 'image' => $this->image()], 3),
        ];

        return $data;
    }

    private function event(int $i): array
    {
        return ['id' => "sample-{$i}", 'title' => "SAMPLE – Event {$i}", 'url' => null, 'category' => null, 'subtitle' => null, 'summary' => self::Text,
            'date' => 'SAMPLE – date', 'datetime' => null, 'time' => null, 'venue' => 'SAMPLE – venue', 'registration_url' => null, 'image' => $this->image()];
    }

    private function post(int $i): array
    {
        return ['id' => "sample-{$i}", 'title' => "SAMPLE – Article {$i}", 'url' => null, 'external' => false, 'type' => 'news', 'excerpt' => self::Text,
            'category' => null, 'category_slug' => null, 'is_featured' => false, 'reading_minutes' => null, 'date' => 'SAMPLE – date', 'datetime' => null, 'image' => $this->image()];
    }

    private function person(int $i): array
    {
        return ['id' => "sample-{$i}", 'name' => "SAMPLE – Faculty name {$i}", 'url' => null, 'designation' => 'SAMPLE – Designation', 'department' => null,
            'phone' => null, 'email' => null, 'photo' => $this->image(), 'qualification' => null, 'research_interests' => null, 'summary' => null, 'category' => 'residential'];
    }

    private function leader(int $i): array
    {
        return ['id' => "sample-{$i}", 'name' => "SAMPLE – Name {$i}", 'url' => null, 'designation' => 'SAMPLE – Designation', 'summary' => null, 'email' => null, 'photo' => $this->image()];
    }

    private function program(int $i): array
    {
        return ['id' => "sample-{$i}", 'title' => "SAMPLE – Program {$i}", 'url' => null, 'summary' => self::Text, 'level' => Program::Levels['postgraduate'] ?? null,
            'award' => null, 'mode' => 'SAMPLE – mode', 'location' => null, 'intake' => 'SAMPLE – intake', 'deadline' => null, 'duration' => 'SAMPLE – duration',
            'duration_detail' => null, 'image' => $this->image(), 'apply_url' => null];
    }

    private function logo(int $i): array
    {
        return ['id' => "sample-{$i}", 'name' => "SAMPLE – Partner {$i}", 'url' => null, 'logo' => null];
    }

    private function publication(int $i): array
    {
        return ['id' => "sample-{$i}", 'title' => "SAMPLE – Publication {$i}", 'url' => null, 'external_url' => null, 'authors' => 'SAMPLE – Authors',
            'publisher' => 'SAMPLE – Publisher', 'date' => 'SAMPLE – date', 'school' => null, 'topic' => null, 'summary' => self::Text, 'image' => $this->image()];
    }

    public function many(callable $make, int $count = 3): array
    {
        return array_map($make, range(1, $count));
    }

    public function image(): ?array
    {
        if ($this->image === false) {
            $this->image = Media::query()->where('kind', 'image')->where('width', '>=', 1200)->latest('id')->first()?->toImage('Sample image');
        }

        return $this->image;
    }
}
