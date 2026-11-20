<?php

use App\Enums\Permission;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\JobApplicationController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\PreviewController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Site\FormController;
use App\Http\Controllers\Site\SearchController;
use App\Http\Controllers\Site\SiteController;
use App\Http\Controllers\Site\SitemapController;
use App\Http\Middleware\EnsureAdminAccess;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', EnsureAdminAccess::class, 'throttle:admin'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('account', AccountController::class)->name('account');

    Route::prefix('content/{resource}')->name('resources.')->controller(ResourceController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{id}/edit', 'edit')->whereNumber('id')->name('edit');
        Route::put('{id}', 'update')->whereNumber('id')->name('update');
        Route::delete('{id}', 'destroy')->whereNumber('id')->name('destroy');
        Route::post('{id}/submit', 'submit')->whereNumber('id')->name('submit');
        Route::post('{id}/publish', 'publish')->whereNumber('id')->name('publish');
        Route::post('{id}/schedule', 'schedule')->whereNumber('id')->name('schedule');
        Route::post('{id}/unpublish', 'unpublish')->whereNumber('id')->name('unpublish');
        Route::post('{id}/discard', 'discard')->whereNumber('id')->name('discard');
        Route::post('{id}/revisions/{revision}/restore', 'restore')->whereNumber(['id', 'revision'])->name('restore');
    });
    Route::get('preview/{resource}/{id}', PreviewController::class)->whereNumber('id')->name('preview');

    Route::middleware('can:'.Permission::MediaManage)->controller(MediaController::class)->group(function () {
        Route::get('media', 'index')->name('media.index');
        Route::get('media/browse', 'browse')->name('media.browse');
        Route::post('media', 'store')->name('media.store');
        Route::get('media/{media}', 'show')->name('media.show');
        Route::put('media/{media}', 'update')->name('media.update');
        Route::delete('media/{media}', 'destroy')->name('media.destroy');
    });

    Route::middleware('can:'.Permission::NavigationManage)->controller(MenuController::class)->group(function () {
        Route::get('menus', 'index')->name('menus.index');
        Route::get('menus/{menu}', 'edit')->name('menus.edit');
        Route::put('menus/{menu}', 'update')->name('menus.update');
    });

    Route::middleware('can:'.Permission::EnquiriesView)->group(function () {
        Route::get('enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('enquiries/export', [EnquiryController::class, 'export'])->name('enquiries.export');
        Route::get('enquiries/{enquiry}', [EnquiryController::class, 'show'])->name('enquiries.show');
        Route::put('enquiries/{enquiry}', [EnquiryController::class, 'update'])->name('enquiries.update');
        Route::post('enquiries/{enquiry}/resend', [EnquiryController::class, 'resend'])->name('enquiries.resend');
        Route::get('newsletter', [NewsletterController::class, 'index'])->name('newsletter.index');
        Route::get('newsletter/export', [NewsletterController::class, 'export'])->name('newsletter.export');
        Route::post('newsletter/{subscriber}/unsubscribe', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
        Route::get('applications', [JobApplicationController::class, 'index'])->name('applications.index');
        Route::get('applications/{application}', [JobApplicationController::class, 'show'])->name('applications.show');
        Route::put('applications/{application}', [JobApplicationController::class, 'update'])->name('applications.update');
        Route::get('applications/{application}/resume', [JobApplicationController::class, 'resume'])->name('applications.resume');
    });

    Route::middleware('can:'.Permission::SettingsManage)->group(function () {
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware('can:'.Permission::UsersManage)->controller(UserController::class)->group(function () {
        Route::get('users', 'index')->name('users.index');
        Route::get('users/create', 'create')->name('users.create');
        Route::post('users', 'store')->name('users.store');
        Route::get('users/{user}/edit', 'edit')->name('users.edit');
        Route::put('users/{user}', 'update')->name('users.update');
        Route::post('users/{user}/reset-two-factor', 'resetTwoFactor')->name('users.reset-2fa');
        Route::post('users/{user}/send-reset', 'sendReset')->name('users.send-reset');
    });

    Route::get('audit', AuditLogController::class)->middleware('can:'.Permission::AuditView)->name('audit');
});

Route::get('sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::middleware('throttle:forms')->group(function () {
    Route::post('enquiries', [FormController::class, 'enquiry'])->name('forms.enquiry');
    Route::post('newsletter', [FormController::class, 'newsletter'])->name('forms.newsletter');
    Route::post('careers/apply', [FormController::class, 'jobApplication'])->name('forms.job-application');
});
Route::get('newsletter/unsubscribe/{token}', [FormController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

Route::get('search', SearchController::class)->middleware('throttle:search')->name('search');

Route::controller(SiteController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('programs', 'programs')->name('programs.index');
    Route::get('programs/{slug}', 'program')->name('programs.show');
    Route::get('school-of-technical-education/iptp/{name}', 'iptpProgram')->where('name', '[a-z0-9-]+')->name('programs.iptp.show');
    Route::get('schools', 'schools')->name('schools.index');
    Route::get('schools/{slug}', 'school')->name('schools.show');
    Route::get('centres/{slug}', 'centre')->name('centres.show');
    Route::get('faculty', 'faculty')->name('faculty.index');
    Route::get('faculty/{slug}', 'facultyProfile')->name('faculty.show');
    Route::get('blog', fn () => redirect('/blogs', 301));
    Route::get('blog/{slug}', fn (string $slug) => redirect('/blogs/'.$slug, 301));
    Route::get('news-events', fn () => redirect('/media', 301));
    Route::get('news-events/{slug}', fn (string $slug) => redirect('/news/'.$slug, 301));
    Route::get('press-release', fn () => redirect('/media?tab=press', 301));
    Route::get('news', 'posts')->defaults('type', 'news')->name('news.index');
    Route::get('news/{slug}', 'post')->defaults('type', 'news')->name('news.show');
    Route::get('blogs', 'blogs')->name('blogs.index');
    Route::get('blogs/{slug}', 'post')->defaults('type', 'blog')->name('blogs.show');
    Route::get('media', 'media')->name('media.index');
    Route::get('media/press', fn () => redirect('/media?tab=press', 301));
    Route::get('media/press/{slug}', 'post')->defaults('type', 'press')->name('press.show');
    Route::get('media/announcements', fn () => redirect('/media?tab=announcement', 301));
    Route::get('media/announcements/{slug}', 'post')->defaults('type', 'announcement')->name('announcements.show');
    Route::get('impact/stories', 'stories')->name('stories.index');
    Route::get('impact/stories/{slug}', 'post')->defaults('type', 'story')->name('stories.show');
    Route::get('events', 'events')->name('events.index');
    Route::get('events/{slug}', 'event')->name('events.show');
    Route::get('research-and-consultancy/publications', 'publications')->name('publications.index');
    Route::get('research-and-consultancy/publications/{slug}', 'publication')->name('publications.show');
    Route::get('about/people/{slug}', 'leader')->name('leaders.show');
    Route::get('careers/{slug}', 'job')->name('jobs.show');
    Route::get('campus/galleries/{slug}', 'gallery')->name('galleries.show');
    Route::get('partnerships/stories/{slug}', 'partnerStory')->name('partners.story');
    Route::get('{path}', 'page')->where('path', '^(?!admin(/|$)|build/|storage/|up$).+')->name('pages.show');
});
