<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\Menu;

class SiteChrome
{
    public static function data(): array
    {
        return ContentCache::remember('site-chrome', fn () => [
            'settings' => SiteSettings::public(),
            'announcements' => Announcement::query()->current()->get(['id', 'label', 'text', 'url'])->toArray(),
            'menus' => Menu::query()->get()->mapWithKeys(fn (Menu $menu) => [$menu->location => $menu->tree()])->all(),
        ]);
    }
}
