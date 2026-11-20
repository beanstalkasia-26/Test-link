<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use App\Support\ContentCache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethodCacheable() || $request->is('admin', 'admin/*')) {
            return $next($request);
        }

        $map = ContentCache::remember('redirects', fn (): array => Redirect::query()->get(['id', 'from_path', 'to_url', 'status_code'])
            ->keyBy('from_path')->map(fn (Redirect $r) => [$r->id, $r->to_url, $r->status_code])->all(), 86400);

        $path = Redirect::normalizePath($request->getPathInfo());
        if (! isset($map[$path])) {
            return $next($request);
        }

        [$id, $to, $status] = $map[$path];
        Redirect::query()->whereKey($id)->update(['hits' => DB::raw('hits + 1'), 'last_hit_at' => now()]);

        $target = $request->getQueryString() && ! str_contains($to, '?') ? $to.'?'.$request->getQueryString() : $to;

        if ($request->header('X-Inertia') && preg_match('#^https?://#i', $target) && parse_url($target, PHP_URL_HOST) !== $request->getHost()) {
            return Inertia::location($target);
        }

        return redirect($target, $status);
    }
}
