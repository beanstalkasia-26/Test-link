<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Author;
use App\Models\Category;
use App\Models\Centre;
use App\Models\Faculty;
use App\Models\Gallery;
use App\Models\Initiative;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Leader;
use App\Models\Media;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Partner;
use App\Models\PartnershipCategory;
use App\Models\Post;
use App\Models\Program;
use App\Models\Publication;
use App\Models\Redirect;
use App\Models\School;
use App\Models\Setting;
use App\Models\Statistic;
use App\Models\Tag;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->registerSchemaMacros();

        Model::shouldBeStrict(! $this->app->isProduction());
        Model::handleLazyLoadingViolationUsing(fn (Model $model, string $relation) => logger()->warning('Lazy loaded '.$model::class.'::'.$relation));

        Relation::enforceMorphMap([
            'user' => User::class,
            'page' => Page::class,
            'post' => Post::class,
            'program' => Program::class,
            'school' => School::class,
            'centre' => Centre::class,
            'faculty' => Faculty::class,
            'partner' => Partner::class,
            'partnership_category' => PartnershipCategory::class,
            'gallery' => Gallery::class,
            'author' => Author::class,
            'category' => Category::class,
            'tag' => Tag::class,
            'initiative' => Initiative::class,
            'statistic' => Statistic::class,
            'announcement' => Announcement::class,
            'menu' => Menu::class,
            'redirect' => Redirect::class,
            'setting' => Setting::class,
            'media' => Media::class,
            'leader' => Leader::class,
            'event' => \App\Models\Event::class,
            'publication' => Publication::class,
            'job_opening' => JobOpening::class,
            'job_application' => JobApplication::class,
        ]);

        Gate::before(fn (User $user): ?bool => $user->is_active && $user->hasRole('super-admin') ? true : null);

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()
            : Password::min(10));

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        if (config('namtech.trusted_proxies')) {
            TrustProxies::at(config('namtech.trusted_proxies'));
        }

        $this->registerRateLimiters();
        $this->registerAuthAuditing();
    }

    private function registerSchemaMacros(): void
    {
        Blueprint::macro('publishable', function (): void {
            $this->string('status', 20)->default('draft')->index();
            $this->string('review_state', 20)->nullable()->index();
            $this->timestamp('published_at')->nullable()->index();
            $this->timestamp('scheduled_for')->nullable()->index();
            $this->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $this->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $this->timestamps();
        });

        Blueprint::macro('seo', function (): void {
            $this->string('meta_title')->nullable();
            $this->string('meta_description', 500)->nullable();
            $this->foreignId('og_image_id')->nullable()->constrained('media')->nullOnDelete();
            $this->string('canonical_url', 2048)->nullable();
            $this->boolean('noindex')->default(false);
        });
    }

    private function registerRateLimiters(): void
    {
        RateLimiter::for('forms', fn (Request $request) => [
            Limit::perMinute(5)->by('forms-minute|'.$request->ip()),
            Limit::perDay(40)->by('forms-day|'.$request->ip()),
        ]);

        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));

        RateLimiter::for('admin', fn (Request $request) => Limit::perMinute(240)->by((string) $request->user()?->id ?: $request->ip()));
    }

    private function registerAuthAuditing(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
                AuditLogger::log('auth.login', $event->user, 'Signed in', [], $event->user);
            }
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user instanceof User) {
                AuditLogger::log('auth.logout', $event->user, 'Signed out', [], $event->user);
            }
        });

        Event::listen(Failed::class, function (Failed $event): void {
            AuditLogger::log('auth.failed', null, 'Failed sign-in attempt', [
                'email' => $event->credentials['email'] ?? null,
            ]);
        });
    }
}
