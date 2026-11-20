<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Program;
use App\Models\Setting;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\PageAssert as Assert;
use Tests\TestCase;

class ShowHideTest extends TestCase
{
    use RefreshDatabase;

    public function test_editors_switch_off_detail_page_blocks(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/content/programs', [
            'title' => 'B.Tech. Robotics', 'slug' => 'btech-robotics', 'level' => 'undergraduate',
            'hidden_blocks' => ['faculty', 'enquire'],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $program = Program::query()->where('slug', 'btech-robotics')->firstOrFail();
        $this->assertSame(['faculty', 'enquire'], $program->hidden_blocks);

        $this->actingAs($admin)->post('/admin/content/programs', [
            'title' => 'Other', 'slug' => 'other', 'level' => 'undergraduate', 'hidden_blocks' => ['not-a-block'],
        ])->assertSessionHasErrors('hidden_blocks.0');

        $this->publish($program);
        auth()->logout();
        $this->get('/programs/btech-robotics')->assertOk()->assertPage(fn (Assert $page) => $page
            ->component('site/programs/Show')
            ->where('hidden', ['faculty', 'enquire']));
    }

    public function test_items_inside_a_section_can_be_hidden_from_the_site(): void
    {
        $admin = $this->admin();
        $sections = [['type' => 'card_grid', 'is_visible' => true, 'data' => ['heading' => 'Cards', 'cards' => [
            ['title' => 'Shown'],
            ['title' => 'Kept in the CMS', '_hidden' => true],
            ['title' => 'Also shown', '_hidden' => false],
        ]]]];

        $this->actingAs($admin)->post('/admin/content/pages', ['title' => 'Cards', 'slug' => 'cards', 'template' => 'default', 'sections' => $sections])
            ->assertRedirect()->assertSessionHasNoErrors();

        $page = Page::query()->where('slug', 'cards')->firstOrFail();
        $cards = $page->sections->first()->data['cards'];
        $this->assertCount(3, $cards);
        $this->assertTrue($cards[1]['_hidden']);
        $this->assertArrayNotHasKey('_hidden', $cards[2]);

        $this->publish($page);
        auth()->logout();
        $this->get('/cards')->assertOk()->assertPage(fn (Assert $p) => $p
            ->has('sections.0.data.cards', 2)
            ->where('sections.0.data.cards.0.title', 'Shown')
            ->where('sections.0.data.cards.1.title', 'Also shown'));
    }

    public function test_site_wide_parts_are_shown_until_switched_off_in_settings(): void
    {
        $this->page('Home', 'home', [], template: 'home');

        $this->get('/')->assertPage(fn (Assert $p) => $p
            ->where('site.settings.show_footer_newsletter', true)
            ->where('site.settings.show_announcement_bar', true));

        $this->actingAs($this->admin())->get('/admin/settings')->assertOk()
            ->assertPage(fn (Assert $p) => $p->where('values.show_footer_newsletter', true));

        $values = array_fill_keys(array_column(array_filter(SiteSettings::fields(), fn ($f) => $f['group'] === 'Show / hide'), 'name'), true);
        $this->actingAs($this->admin())->put('/admin/settings', ['site_name' => 'NAMTECH', 'show_footer_newsletter' => false] + $values)
            ->assertSessionHas('success');
        $this->assertFalse(Setting::get('show_footer_newsletter'));

        auth()->logout();
        $this->get('/')->assertPage(fn (Assert $p) => $p
            ->where('site.settings.show_footer_newsletter', false)
            ->where('site.settings.show_announcement_bar', true));
    }
}
