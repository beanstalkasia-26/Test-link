<?php

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->sections()->each(function (PageSection $section): void {
            $section->data = array_merge($section->data ?? [], [
                'cta2_label' => 'Corporate Partnership',
                'cta2_url' => '/partnerships/corporate',
                'cta3_label' => 'Academic Partnership',
                'cta3_url' => '/partnerships/academic',
            ]);
            $section->saveQuietly();
        });
    }

    public function down(): void
    {
        $this->sections()->each(function (PageSection $section): void {
            $section->data = array_diff_key($section->data ?? [], array_flip(['cta2_label', 'cta2_url', 'cta3_label', 'cta3_url']));
            $section->saveQuietly();
        });
    }

    private function sections()
    {
        return PageSection::query()
            ->where('sectionable_type', 'page')
            ->where('sectionable_id', Page::query()->where('slug', 'home')->value('id'))
            ->where('type', 'partners_showcase')
            ->get();
    }
};
