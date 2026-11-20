<?php

namespace App\Http\Middleware;

use App\Support\AdminSession;
use App\Support\SiteChrome;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    protected $withoutSsr = ['admin', 'admin/*'];

    public function share(Request $request): array
    {
        $isAdmin = $request->is('admin', 'admin/*');
        $isCmsScreen = $isAdmin && ! $request->is('admin/preview/*');

        return [
            ...parent::share($request),
            'site' => $isCmsScreen ? null : fn () => SiteChrome::data(),
            'auth' => $isAdmin ? fn () => AdminSession::user($request) : null,
            'navigation' => $isCmsScreen ? fn () => AdminSession::navigation($request) : [],
            'flash' => fn () => $this->flash($request),
        ];
    }

    private function flash(Request $request): array
    {
        if (! $request->hasSession()) {
            return [];
        }

        $session = $request->session();

        return array_filter([
            'success' => $session->get('success'),
            'error' => $session->get('error'),
            'status' => $session->get('status'),
            'form' => $session->get('form_success'),
        ], fn ($value) => $value !== null);
    }
}
