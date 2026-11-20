<?php

namespace App\Http\Responses;

use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class PageResponse
{
    public static function render(string $component, array $props = [], ?array $seo = null, int $status = 200): Response
    {
        if ($seo !== null) {
            $props['seo'] = array_merge($seo, $props['seo'] ?? []);
        }

        return Inertia::render($component, $props)->toResponse(request())->setStatusCode($status);
    }
}
