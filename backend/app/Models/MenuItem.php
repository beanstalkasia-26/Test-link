<?php

namespace App\Models;

use App\Models\Concerns\FlushesPublicCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use FlushesPublicCache;

    protected $fillable = ['menu_id', 'parent_id', 'page_id', 'label', 'url', 'opens_in_new_tab', 'is_visible', 'sort_order'];

    protected function casts(): array
    {
        return ['opens_in_new_tab' => 'boolean', 'is_visible' => 'boolean'];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function resolvedUrl(): ?string
    {
        if ($this->page_id) {
            return $this->page && $this->page->isLive() ? $this->page->publicPath() : null;
        }

        return $this->url ?: null;
    }

    public function isExternal(): bool
    {
        $url = (string) $this->url;

        return ! $this->page_id && preg_match('#^https?://#i', $url) === 1
            && parse_url($url, PHP_URL_HOST) !== parse_url(config('app.url'), PHP_URL_HOST);
    }
}
