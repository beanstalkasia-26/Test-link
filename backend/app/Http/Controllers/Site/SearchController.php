<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Page;
use App\Models\Post;
use App\Models\Program;
use App\Models\School;
use App\Services\PublicRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    public function __invoke(Request $request, PublicRenderer $renderer): Response
    {
        $query = Str::limit(trim((string) $request->query('q', '')), 100, '');
        $results = [];

        if (mb_strlen($query) >= 2) {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $query).'%';
            $match = fn (array $columns) => fn (Builder $q) => $q->where(function (Builder $q) use ($columns, $like) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', $like);
                }
            });

            $results = collect()
                ->concat(Program::query()->published()->where($match(['title', 'summary', 'award']))->limit(10)->get()
                    ->map(fn (Program $p) => ['type' => 'Program', 'title' => $p->title, 'url' => $p->publicPath(), 'excerpt' => $p->summary]))
                ->concat(Page::query()->published()->where($match(['title', 'excerpt']))->limit(10)->get()
                    ->map(fn (Page $p) => ['type' => 'Page', 'title' => $p->title, 'url' => $p->publicPath(), 'excerpt' => $p->excerpt]))
                ->concat(School::query()->published()->where($match(['name', 'summary']))->limit(5)->get()
                    ->map(fn (School $s) => ['type' => 'School', 'title' => $s->name, 'url' => $s->publicPath(), 'excerpt' => $s->summary]))
                ->concat(Faculty::query()->published()->where($match(['name', 'designation', 'research_interests']))->limit(10)->get()
                    ->map(fn (Faculty $f) => ['type' => 'Faculty', 'title' => $f->name, 'url' => $f->publicPath(), 'excerpt' => $f->designation]))
                ->concat(Post::query()->published()->where($match(['title', 'excerpt']))->latest('published_at')->limit(10)->get()
                    ->map(fn (Post $p) => ['type' => Post::Types[$p->type]['label'] ?? 'News', 'title' => $p->title, 'url' => $p->externalLink() ?: $p->publicPath(), 'excerpt' => $p->excerpt]))
                ->values()->all();
        }

        return $renderer->respond('site/Search', [
            'query' => $query,
            'results' => $results,
        ], ['title' => $query ? 'Search results for “'.$query.'”' : 'Search', 'noindex' => true, 'canonical' => url('/search')]);
    }
}
