<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\PartnershipPageDesign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\PageAssert as Assert;
use Tests\TestCase;

class PartnershipDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_partnership_design_keeps_saved_copy_and_supplies_missing_modules_once(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $landing = $this->publish(Page::create(['title' => 'Partnerships', 'slug' => 'partnerships']));
        $corporate = $this->publish(Page::create(['title' => 'Corporate Partnerships', 'slug' => 'corporate', 'parent_id' => $landing->id]));
        $models = $this->publish(Page::create(['title' => 'Schools, Centers, CoEs & Lab Partnerships', 'slug' => 'schools-centers-coes-and-lab-partnerships', 'parent_id' => $corporate->id]));
        $modelTabs = $models->sections()->create([
            'type' => 'tabs', 'is_visible' => true, 'sort_order' => 0,
            'data' => ['heading' => 'Partnership Models', 'layout' => 'standard', 'tabs' => [['label' => 'School-level Partnerships', 'body' => '<p>Saved school partnership copy.</p>']]],
        ]);
        $page = $this->publish(Page::create(['title' => 'Sponsored Research', 'slug' => 'sponsored-research', 'parent_id' => $corporate->id]));
        $page->sections()->create([
            'type' => 'page_hero', 'is_visible' => true, 'sort_order' => 0,
            'data' => ['title' => 'Sponsored Research', 'body' => 'Existing sponsored research copy.'],
        ]);
        $page->sections()->create([
            'type' => 'card_grid', 'is_visible' => false, 'sort_order' => 1,
            'data' => ['heading' => 'How We Work Together', 'cards' => [['title' => 'Existing Research Program', 'body' => 'Saved description.']]],
        ]);

        $design = app(PartnershipPageDesign::class);
        $design->apply();
        $design->apply();

        $sections = $page->sections()->get();
        $this->assertSame(
            ['page_hero', 'logo_strip', 'media_cards', 'partners_showcase', 'testimonials', 'news_carousel', 'contact_band'],
            $sections->pluck('type')->all(),
        );
        $this->assertTrue($sections->every('is_visible'));
        $this->assertSame('Existing sponsored research copy.', $sections[0]->data['body']);
        $this->assertSame('Existing Research Program', $sections[2]->data['items'][0]['title']);
        $this->assertSame('Saved description.', $sections[2]->data['items'][0]['body']);
        $this->assertSame([$modelTabs->id], $models->sections()->where('type', 'tabs')->pluck('id')->all());
        $this->assertSame('section_panels', $modelTabs->fresh()->data['layout']);
        $this->assertSame('<p>Saved school partnership copy.</p>', $modelTabs->fresh()->data['tabs'][0]['body']);

        $this->get('/partnerships/corporate/sponsored-research')->assertOk()->assertPage(fn (Assert $page) => $page
            ->where('sections.1.data.logos.0.name', 'Sample partner')
            ->where('sections.3.data.stories.0.name', 'Sample industry collaboration')
            ->where('sections.5.data.posts.0.type', 'Sample insight'));
    }
}
