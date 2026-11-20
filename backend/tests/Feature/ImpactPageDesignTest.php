<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Statistic;
use App\Services\ImpactPageDesign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\PageAssert as Assert;
use Tests\TestCase;

class ImpactPageDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_impact_design_preserves_editorial_copy_and_adds_each_module_once(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $impact = $this->publish(Page::create(['title' => 'Impact', 'slug' => 'impact']));
        $impact->sections()->create([
            'type' => 'page_hero', 'is_visible' => true, 'sort_order' => 0,
            'data' => ['title' => 'Power of a Collaborative Institution', 'body' => 'An approved description of our impact.'],
        ]);

        foreach (['14' => 'Collaborative initiatives', '93%' => 'Participant feedback', '879+' => 'Learners reached'] as $value => $label) {
            Statistic::create(['group' => 'impact', 'value' => $value, 'label' => $label, 'is_approved' => true]);
        }

        app(ImpactPageDesign::class)->apply();
        app(ImpactPageDesign::class)->apply();

        $this->assertSame(
            ['page_hero', 'card_grid', 'media_cards', 'image_mosaic', 'media_cards', 'logo_strip', 'card_grid', 'media_cards', 'testimonials', 'cta_band'],
            $impact->sections()->get()->pluck('type')->all(),
        );
        $this->get('/impact')->assertOk()->assertPage(fn (Assert $page) => $page
            ->where('sections.0.data.body', 'An approved description of our impact.')
            ->has('sections.0.data.statistics', 3)
            ->where('sections.5.data.logos.0.name', 'Sample partner'));

        $caseStudy = Post::query()->where('slug', 'sample-manufacturing-case-study')->firstOrFail();
        $this->assertSame('industry-case-studies', $caseStudy->category->slug);
        $this->assertSame(1, Post::query()->where('type', 'story')->count());
        $this->assertTrue($caseStudy->sections()->where('anchor', 'case-study-solutions')->exists());
    }
}
