<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || $user->roles->isEmpty()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/admin/login')->with('error', 'Your account does not have access to the admin area.');
        }

        if ($user->requiresTwoFactor() && ! $user->hasConfirmedTwoFactor() && ! $request->routeIs('admin.account', 'two-factor.*', 'password.confirm*', 'logout', 'user-password.update')) {
            return to_route('admin.account')->with('error', 'Your role requires two-factor authentication. Please set it up to continue.');
        }

        return $next($request);
    }
}
