<?php

use App\Models\Page;
use App\Models\Program;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $page = Page::query()->where('path', 'programs')->first();
        if (! $page || $page->sections()->where('type', 'program_levels')->exists()) {
            return;
        }

        $image = fn (string $level) => Program::query()->published()->where('level', $level)->whereNotNull('image_id')->orderBy('sort_order')->value('image_id');
        $order = (int) $page->sections()->max('sort_order');

        $sections = [
            ['type' => 'program_levels', 'is_visible' => true, 'data' => ['items' => [
                ['level' => 'undergraduate', 'heading' => 'Bachelors Programmes', 'body' => null, 'image' => $image('undergraduate')],
                ['level' => 'postgraduate', 'heading' => 'Masters Programmes', 'body' => null, 'image' => $image('postgraduate')],
                ['level' => 'executive', 'heading' => 'Executive Programmes', 'body' => null, 'image' => $image('executive')],
                ['level' => 'technical', 'heading' => 'Industry Programmes (iPTP)', 'body' => null, 'image' => $image('technical')],
            ]]],
            ['type' => 'card_grid', 'is_visible' => true, 'data' => [
                'heading' => 'NAMTECH’S Unique Teaching Style', 'body' => null, 'columns' => '3', 'tone' => 'white', 'style' => 'joined',
                'cards' => [
                    ['icon' => 'people', 'title' => 'Interactive Lectures', 'body' => 'Learn by engaging with faculty, peers, and live challenges that mirror real-world decision-making.', 'link_url' => null],
                    ['icon' => 'robot', 'title' => 'Hands-on Labs', 'body' => 'Learning happens by building. At NAMTECH, classrooms don’t have desks but machines used in the industry.', 'link_url' => null],
                    ['icon' => 'globe', 'title' => 'Global immersion', 'body' => 'Our curriculum is co-designed and co-delivered with academicians from global universities.', 'link_url' => null],
                ],
            ]],
            ['type' => 'faq', 'is_visible' => false, 'data' => [
                'heading' => 'Frequently Asked Questions', 'body' => 'Find answers to common questions about the Program, admissions, and student life.', 'layout' => 'centered',
                'items' => array_map(fn (string $q) => ['question' => $q, 'answer' => null], [
                    'What is the application process for programmes?',
                    'Are the programmes available in online or on-campus mode?',
                    'What are the tuition fees and payment options?',
                    'Who can I contact for admission-related queries?',
                    'Do the programs offer certificates, placements, or career support?',
                ]),
            ]],
            ['type' => 'cta_band', 'is_visible' => true, 'data' => ['heading' => 'Explore Our Schools', 'body' => null, 'cta_label' => 'View all schools', 'cta_url' => '/schools']],
        ];

        foreach ($sections as $section) {
            $page->sections()->create($section + ['sort_order' => ++$order]);
        }
    }

    public function down(): void
    {
        Page::query()->where('path', 'programs')->first()?->sections()->whereIn('type', ['program_levels'])->delete();
    }
};
