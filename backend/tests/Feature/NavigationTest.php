<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    private function item(string $label, ?string $url, array $children = []): array
    {
        return ['label' => $label, 'url' => $url, 'page_id' => null, 'opens_in_new_tab' => false, 'is_visible' => true, 'children' => $children];
    }

    public function test_header_menus_support_three_levels_for_mega_menus(): void
    {
        $menu = Menu::create(['name' => 'Main', 'location' => 'primary']);
        $publisher = $this->admin(Role::Publisher);

        $this->actingAs($publisher)->put("/admin/menus/{$menu->id}", ['items' => [
            $this->item('Programs', '/programs', [
                $this->item('UG Programs', '/programs?level=undergraduate', [$this->item('B.Tech in Robotics', '/programs/robotics')]),
                $this->item('PG Programs', '/programs?level=postgraduate'),
            ]),
        ]])->assertSessionHas('success');

        $this->page('About', 'about');
        auth()->logout();
        $this->get('/about')
            ->assertJsonPath('props.site.menus.primary.0.label', 'Programs')
            ->assertJsonPath('props.site.menus.primary.0.children.0.label', 'UG Programs')
            ->assertJsonPath('props.site.menus.primary.0.children.0.children.0.url', '/programs/robotics')
            ->assertJsonPath('props.site.menus.primary.0.children.1.label', 'PG Programs');
    }

    public function test_menu_depth_is_limited_per_location(): void
    {
        $footer = Menu::create(['name' => 'Footer', 'location' => 'footer_quick_links']);
        $primary = Menu::create(['name' => 'Main', 'location' => 'primary']);
        $publisher = $this->admin(Role::Publisher);

        $this->actingAs($publisher)->put("/admin/menus/{$footer->id}", ['items' => [$this->item('A', '/a', [$this->item('B', '/b')])]])
            ->assertSessionHasErrors('items.0.children');

        $this->actingAs($publisher)->put("/admin/menus/{$primary->id}", ['items' => [
            $this->item('A', '/a', [$this->item('B', '/b', [$this->item('C', '/c', [$this->item('D', '/d')])])]),
        ]])->assertSessionHasErrors('items.0.children.0.children.0.children');
    }

    public function test_menu_links_reject_unsafe_urls(): void
    {
        $menu = Menu::create(['name' => 'Main', 'location' => 'primary']);

        $this->actingAs($this->admin(Role::Publisher))->put("/admin/menus/{$menu->id}", ['items' => [$this->item('Bad', 'javascript:alert(1)')]])
            ->assertSessionHasErrors('items.0.url');
    }
}
