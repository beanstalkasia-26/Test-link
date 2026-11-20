<?php

use App\Enums\ContentStatus;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Program;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->apply(true);
    }

    public function down(): void
    {
        $this->apply(false);
    }

    private function apply(bool $hide): void
    {
        $slugs = Program::query()->where('level', 'undergraduate')->pluck('slug')->all();

        Program::query()->where('level', 'undergraduate')
            ->update(['status' => ($hide ? ContentStatus::Unpublished : ContentStatus::Published)->value]);

        $urls = array_merge(['/programs?level=undergraduate'], array_map(fn (string $slug) => "/programs/{$slug}", $slugs));
        $parents = MenuItem::query()->whereIn('url', $urls)->pluck('id');
        MenuItem::query()->where(fn ($q) => $q->whereIn('url', $urls)->orWhereIn('parent_id', $parents))
            ->update(['is_visible' => ! $hide]);

        PageSection::query()->whereIn('type', ['media_cards', 'program_levels'])->each(function (PageSection $section) use ($hide, $slugs): void {
            $data = $section->data ?? [];
            $changed = false;

            foreach ($data['items'] ?? [] as $i => $item) {
                $undergraduate = ($item['level'] ?? null) === 'undergraduate'
                    || in_array(basename((string) ($item['link_url'] ?? '')), $slugs, true);
                if ($undergraduate && (bool) ($item['_hidden'] ?? false) !== $hide) {
                    $data['items'][$i]['_hidden'] = $hide;
                    $changed = true;
                }
            }

            if ($changed) {
                $section->data = $data;
                $section->saveQuietly();
            }
        });

        $admissions = Page::query()->where('slug', 'admissions')->value('id');
        PageSection::query()->where('sectionable_type', 'page')->where('sectionable_id', $admissions)
            ->where('type', 'program_finder')->get()
            ->filter(fn (PageSection $section) => ($section->data['level'] ?? null) === 'undergraduate')
            ->each(fn (PageSection $section) => $section->forceFill(['is_visible' => ! $hide])->saveQuietly());

        PageSection::query()->where('sectionable_type', 'page')->where('sectionable_id', $admissions)
            ->where('type', 'page_hero')->get()
            ->each(function (PageSection $section) use ($hide): void {
                $data = $section->data ?? [];
                [$from, $to] = $hide ? ['Admissions: Undergraduate', 'Admissions'] : ['Admissions', 'Admissions: Undergraduate'];
                if (($data['title'] ?? null) === $from) {
                    $data['title'] = $to;
                    $section->data = $data;
                    $section->saveQuietly();
                }
            });
    }
};
