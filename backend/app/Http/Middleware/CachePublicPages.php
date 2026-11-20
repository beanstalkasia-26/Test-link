<?php

namespace App\Http\Middleware;

use App\Support\ContentCache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class CachePublicPages
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isCacheable($request)) {
            return $next($request);
        }

        $manifestPath = public_path('build/manifest.json');
        $manifestHash = is_file($manifestPath) ? (string) md5_file($manifestPath) : 'v1';

        $key = ContentCache::key('page:'.sha1(implode('|', [
            $request->getSchemeAndHttpHost(),
            $request->getRequestUri(),
            $request->header('X-Inertia', ''),
            $request->header('X-Inertia-Version', ''),
            $request->header('X-Inertia-Partial-Component', ''),
            $request->header('X-Inertia-Partial-Data', ''),
            $manifestHash,
        ])));

        if ($cached = Cache::get($key)) {
            return response($cached['content'], 200, $cached['headers'] + ['X-Page-Cache' => 'HIT']);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200 && $this->isStillCacheable($request) && $this->isComplete($request, $response)) {
            Cache::put($key, [
                'content' => $response->getContent(),
                'headers' => array_filter([
                    'Content-Type' => $response->headers->get('Content-Type'),
                    'Vary' => $response->headers->get('Vary'),
                    'X-Inertia' => $response->headers->get('X-Inertia'),
                ]),
            ], config('namtech.page_cache_ttl'));
            $response->headers->set('X-Page-Cache', 'MISS');
        }

        return $response;
    }

    private function isCacheable(Request $request): bool
    {
        return config('namtech.page_cache_enabled')
            && $request->isMethodCacheable()
            && ! $request->is('admin', 'admin/*', 'up')
            && $request->user() === null
            && ! (app()->isLocal() && Vite::isRunningHot())
            && $this->isStillCacheable($request);
    }

    private function isComplete(Request $request, Response $response): bool
    {
        return $request->header('X-Inertia') !== null || str_contains((string) $response->getContent(), 'data-server-rendered');
    }

    private function isStillCacheable(Request $request): bool
    {
        $session = $request->hasSession() ? $request->session() : null;

        return ! $session || (! $session->has('errors') && ! $session->has('form_success') && ! $session->has('success') && ! $session->has('error'));
    }
}
