@php
    // CMS screens use the admin style sheet; the public site and CMS previews use the site one.
    $isAdmin = request()->is('admin', 'admin/*') && ! request()->is('admin/preview/*');
    $preload = $page['props']['seo']['preloadImage'] ?? null;
@endphp
<!DOCTYPE html>
<html lang="en-IN">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#5D24FC">
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/images/favicon.svg" type="image/svg+xml">
        @if ($isAdmin)
            <meta name="robots" content="noindex, nofollow">
        @endif
        @if ($preload)
            <link rel="preload" as="image" type="{{ $preload['type'] }}" imagesrcset="{{ $preload['srcset'] }}" imagesizes="{{ $preload['sizes'] }}" fetchpriority="high">
        @endif
        @viteReactRefresh
        @vite([$isAdmin ? 'src/styles/admin.scss' : 'src/styles/site.scss', 'src/app.jsx'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
