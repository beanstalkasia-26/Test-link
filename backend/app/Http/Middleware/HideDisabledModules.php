<?php

namespace App\Http\Middleware;

use App\Support\SiteSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Returns 404 for any public module an admin has switched off under Settings → Show / hide modules. */
class HideDisabledModules
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin', 'admin/*', 'up') && SiteSettings::pathIsHidden($request->path())) {
            abort(404);
        }

        return $next($request);
    }
}
