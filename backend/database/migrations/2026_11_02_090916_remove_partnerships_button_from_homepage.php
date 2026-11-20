<?php

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->setButton(null, null);
    }

    public function down(): void
    {
        $this->setButton('Partnerships at NAMTECH', '/partnerships');
    }

    private function setButton(?string $label, ?string $url): void
    {
        PageSection::query()
            ->where('sectionable_type', 'page')
            ->where('sectionable_id', Page::query()->where('slug', 'home')->value('id'))
            ->where('type', 'partners_showcase')
            ->get()
            ->each(function (PageSection $section) use ($label, $url): void {
                $section->data = array_merge($section->data ?? [], ['cta_label' => $label, 'cta_url' => $url]);
                $section->saveQuietly();
            });
    }
};
