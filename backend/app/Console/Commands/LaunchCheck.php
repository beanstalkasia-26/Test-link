<?php

namespace App\Console\Commands;

use App\Admin\ResourceRegistry;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Statistic;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('content:launch-check')]
#[Description('Report placeholder content, missing settings and other pre-launch issues')]
class LaunchCheck extends Command
{
    private const Placeholders = ['content to be confirmed', 'lorem ipsum', 'tbc', '[placeholder'];

    public function handle(): int
    {
        $issues = [];

        foreach (ResourceRegistry::all() as $resource) {
            if (! $resource->isPublishable()) {
                continue;
            }
            $resource->model()::query()->get()->each(function ($model) use ($resource, &$issues) {
                $text = Str::lower(json_encode($model->toSnapshot(), JSON_UNESCAPED_UNICODE));
                foreach (self::Placeholders as $marker) {
                    if (preg_match('/(?<![a-z])'.preg_quote($marker, '/').'(?![a-z])/', $text)) {
                        $issues[] = [$resource->singular(), $model->displayTitle(), 'Contains placeholder text ("'.$marker.'")'];
                        break;
                    }
                }
            });
        }

        Page::query()->with('sections')->get()->each(function (Page $page) use (&$issues) {
            if (in_array($page->template, ['legal'], true) && ! $page->isLive()) {
                $issues[] = ['Page', $page->title, 'Legal page is not published'];
            }
            foreach ($page->sections as $section) {
                if (in_array($section->type, ['intro', 'rich_text'], true) && empty($section->data['body'])) {
                    $issues[] = ['Page', $page->title, 'Section "'.($section->data['heading'] ?? $section->type).'" has no text'];
                }
            }
        });

        foreach (['linkedin_url' => 'LinkedIn URL', 'x_url' => 'X URL', 'youtube_url' => 'YouTube URL', 'virtual_tour_url' => 'Virtual tour URL', 'map_url' => 'Map link'] as $key => $label) {
            if (! Setting::get($key)) {
                $issues[] = ['Setting', $label, 'Not configured'];
            }
        }

        foreach (MenuItem::query()->where('is_visible', false)->whereNull('page_id')->whereNull('url')->get() as $item) {
            $issues[] = ['Menu item', $item->label, 'Hidden until a link is configured'];
        }

        foreach (Media::query()->where('kind', '<>', 'document')->where(fn ($q) => $q->whereNull('alt')->orWhere('alt', ''))->has('usages')->get() as $media) {
            $issues[] = ['Image', $media->original_name, 'Used on the site without alt text'];
        }

        foreach (Statistic::query()->where('is_approved', false)->get() as $stat) {
            $issues[] = ['Statistic', $stat->displayTitle(), 'Not approved – hidden from the site'];
        }

        if ($issues === []) {
            $this->info('No launch issues found.');

            return self::SUCCESS;
        }

        $this->table(['Type', 'Item', 'Issue'], $issues);
        $this->warn(count($issues).' item(s) need attention before launch.');

        return self::SUCCESS;
    }
}
