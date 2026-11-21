<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_a_module_off_returns_404_and_back_on_restores_it(): void
    {
        $this->get('/events')->assertOk();

        Setting::put('module_events', false, 'Show / hide modules');
        $this->get('/events')->assertNotFound();
        $this->get('/events/anything')->assertNotFound();
        $this->get('/blogs')->assertOk();

        Setting::put('module_events', true, 'Show / hide modules');
        $this->get('/events')->assertOk();
    }

    public function test_admin_area_is_never_hidden_by_module_switches(): void
    {
        Setting::put('module_events', false, 'Show / hide modules');
        $this->actingAs($this->admin())->get('/admin/content/events')->assertOk();
    }

    public function test_every_role_must_use_two_factor(): void
    {
        $this->assertEqualsCanonicalizing(['super-admin', 'publisher', 'editor'], config('namtech.two_factor_roles'));
    }
}
