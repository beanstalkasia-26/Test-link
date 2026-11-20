<?php

namespace Tests;

use App\Enums\ContentStatus;
use App\Enums\Role;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(Request::create('/')),
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer' => config('app.url').'/',
        ]);
        TestResponse::macro('assertPage', function (callable $assertions) {
            $assertions(new PageAssert($this));

            return $this;
        });
    }

    protected function admin(Role $role = Role::SuperAdmin): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role->value);

        return $user;
    }

    protected function publish(Model $model): Model
    {
        $model->forceFill(['status' => ContentStatus::Published, 'published_at' => now()->subMinute()])->save();

        return $model->fresh();
    }

    protected function page(string $title, string $slug, array $sections = [], bool $live = true, string $template = 'default'): Page
    {
        $page = Page::create(['title' => $title, 'slug' => $slug, 'template' => $template]);
        foreach ($sections as $i => [$type, $data]) {
            $page->sections()->create(['type' => $type, 'data' => $data, 'sort_order' => $i, 'is_visible' => true]);
        }

        return $live ? $this->publish($page) : $page;
    }
}
