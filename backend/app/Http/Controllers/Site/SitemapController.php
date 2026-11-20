<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Centre;
use App\Models\Faculty;
use App\Models\Gallery;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Program;
use App\Models\Redirect;
use App\Models\School;
use App\Support\ContentCache;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $xml = ContentCache::remember('sitemap', function (): string {
            $urls = collect([['loc' => url('/'), 'lastmod' => null]])
                ->concat(collect(['programs', 'schools', 'faculty', 'news', 'blogs'])->map(fn ($p) => ['loc' => url($p), 'lastmod' => null]));

            $redirected = Redirect::query()->pluck('from_path')->flip();

            foreach ([Page::class, Program::class, School::class, Centre::class, Faculty::class, Post::class, Gallery::class, Partner::class] as $class) {
                $class::query()->published()->where('noindex', false)->get()->each(function ($model) use (&$urls, $redirected) {
                    if ($model instanceof Page && $model->template === 'home') {
                        return;
                    }
                    if ($model instanceof Post && $model->externalLink()) {
                        return;
                    }
                    if (($path = $model->publicPath()) && ! $redirected->has(Redirect::normalizePath($path))) {
                        $urls->push(['loc' => url($path), 'lastmod' => $model->updated_at?->toAtomString()]);
                    }
                });
            }

            $body = $urls->unique('loc')->map(fn ($u) => '<url><loc>'.e($u['loc']).'</loc>'.($u['lastmod'] ? '<lastmod>'.$u['lastmod'].'</lastmod>' : '').'</url>')->implode('');

            return '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$body.'</urlset>';
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /search', 'Sitemap: '.url('sitemap.xml')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }
}
