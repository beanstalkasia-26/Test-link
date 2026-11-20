<?php

namespace Tests\Feature;

use App\Admin\ResourceRegistry;
use App\Enums\Role;
use App\Models\Enquiry;
use App\Models\Menu;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_cms_screen_returns_a_page_envelope(): void
    {
        Queue::fake();
        $admin = $this->admin(Role::SuperAdmin);
        $page = $this->page('About', 'about', [['intro', ['heading' => 'About NAMTECH', 'body' => '<p>Text</p>']]]);
        $program = $this->publish(Program::create(['title' => 'B.Tech Robotics', 'slug' => 'btech-robotics', 'level' => 'undergraduate']));
        $menu = Menu::create(['name' => 'Primary', 'location' => 'primary']);
        $this->post('/enquiries', ['type' => 'contact', 'name' => 'Asha Patel', 'email' => 'asha@example.com', 'message' => 'I would like to visit the campus.', 'consent' => true, 'website' => '', 'elapsed' => 6000]);
        $this->post('/newsletter', ['newsletter_email' => 'reader@example.com', 'newsletter_consent' => true]);
        $enquiry = Enquiry::query()->firstOrFail();

        $screens = ['/admin', '/admin/account', '/admin/menus', "/admin/menus/{$menu->id}", '/admin/media', '/admin/enquiries', "/admin/enquiries/{$enquiry->id}",
            '/admin/newsletter', '/admin/applications', '/admin/settings', '/admin/users', '/admin/users/create', "/admin/users/{$admin->id}/edit", '/admin/audit',
            "/admin/content/pages/{$page->id}/edit", "/admin/content/programs/{$program->id}/edit", "/admin/preview/pages/{$page->id}"];
        foreach (ResourceRegistry::all() as $resource) {
            $screens[] = '/admin/content/'.$resource->slug();
            $screens[] = '/admin/content/'.$resource->slug().'/create';
        }

        $envelopes = [];
        foreach ($screens as $screen) {
            $response = $this->actingAs($admin)->get($screen);
            $response->assertOk()->assertJsonStructure(['component', 'props']);
            $envelopes[$screen] = $response->json();
        }

        $this->actingAs($admin)->get('/admin')->assertOk()->assertJsonPath('props.auth.user.id', $admin->id)->assertJsonStructure(['props' => ['navigation']]);
        if ($file = getenv('DUMP_ADMIN_SCREENS')) {
            file_put_contents($file, json_encode($envelopes, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
        $this->assertCount(count($screens), $envelopes);
    }
}
