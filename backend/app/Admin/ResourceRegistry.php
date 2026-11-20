<?php

namespace App\Admin;

use App\Admin\Resources\Resource;

class ResourceRegistry
{
    public static function map(): array
    {
        return [
            'pages' => Resources\PageResource::class,
            'announcements' => Resources\AnnouncementResource::class,
            'redirects' => Resources\RedirectResource::class,
            'programs' => Resources\ProgramResource::class,
            'schools' => Resources\SchoolResource::class,
            'centres' => Resources\CentreResource::class,
            'faculty' => Resources\FacultyResource::class,
            'leaders' => Resources\LeaderResource::class,
            'partners' => Resources\PartnerResource::class,
            'partnership-categories' => Resources\PartnershipCategoryResource::class,
            'posts' => Resources\PostResource::class,
            'categories' => Resources\CategoryResource::class,
            'tags' => Resources\TagResource::class,
            'authors' => Resources\AuthorResource::class,
            'events' => Resources\EventResource::class,
            'publications' => Resources\PublicationResource::class,
            'jobs' => Resources\JobOpeningResource::class,
            'galleries' => Resources\GalleryResource::class,
            'initiatives' => Resources\InitiativeResource::class,
            'statistics' => Resources\StatisticResource::class,
        ];
    }

    public static function get(string $slug): Resource
    {
        $class = self::map()[$slug] ?? abort(404);

        return new $class;
    }

    public static function all(): array
    {
        return array_map(fn (string $class) => new $class, array_values(self::map()));
    }

    public static function forModel(object $model): ?Resource
    {
        foreach (self::all() as $resource) {
            if ($resource->model() === $model::class) {
                return $resource;
            }
        }

        return null;
    }
}
