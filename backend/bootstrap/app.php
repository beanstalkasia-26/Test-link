<?php

use App\Http\Middleware\CachePublicPages;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HandleRedirects;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Responses\PageResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->web(append: [
            HandleRedirects::class,
            HandleInertiaRequests::class,
            CachePublicPages::class,
        ]);
        $middleware->redirectGuestsTo('/admin/login');
        $middleware->redirectUsersTo('/admin');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $status = $response->getStatusCode();
            $friendly = in_array($status, [403, 404, 419, 429, 500, 503], true);
            $isJsonRequest = $request->expectsJson() && ! $request->header('X-Inertia');

            if (! $friendly || $isJsonRequest || ($status >= 500 && config('app.debug'))) {
                return $response;
            }

            return PageResponse::render('Error', [
                'status' => $status,
                'admin' => $request->is('admin', 'admin/*'),
                'message' => $status === 419 ? 'Your session expired. Please try again.' : null,
            ], ['title' => 'Error '.$status.' | NAMTECH', 'noindex' => true], $status);
        });
    })->create();

$sharedHostingPublic = dirname(__DIR__, 2).'/public_html';
if (! is_dir(dirname(__DIR__).'/public') && is_dir($sharedHostingPublic)) {
    $app->usePublicPath($sharedHostingPublic);
}

return $app;
