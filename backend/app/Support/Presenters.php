<?php

namespace App\Support;

use App\Models\Centre;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\JobOpening;
use App\Models\Leader;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Program;
use App\Models\Publication;
use App\Models\School;

class Presenters
{
    public static function programCard(Program $program): array
    {
        return [
            'id' => $program->id,
            'title' => $program->title,
            'url' => $program->publicPath(),
            'summary' => $program->summary,
            'level' => $program->levelLabel(),
            'award' => $program->award,
            'mode' => $program->mode,
            'location' => $program->location,
            'intake' => $program->intake,
            'deadline' => $program->application_deadline?->format('F j, Y'),
            'duration' => $program->duration,
            'duration_detail' => $program->duration_detail,
            'image' => $program->image?->toImage($program->title),
            'apply_url' => $program->apply_url,
        ];
    }

    public static function schoolCard(School $school): array
    {
        return [
            'id' => $school->id,
            'title' => $school->name,
            'url' => $school->publicPath(),
            'summary' => $school->summary,
            'tagline' => $school->tagline,
            'image' => $school->image?->toImage(),
        ];
    }

    public static function centreCard(Centre $centre): array
    {
        return [
            'id' => $centre->id,
            'title' => $centre->name,
            'url' => $centre->publicPath(),
            'summary' => $centre->summary,
            'tagline' => $centre->tagline,
            'image' => $centre->image?->toImage(),
        ];
    }

    public static function facultyCard(Faculty $faculty): array
    {
        return [
            'id' => $faculty->id,
            'name' => $faculty->name,
            'url' => $faculty->publicPath(),
            'designation' => $faculty->designation,
            'department' => $faculty->department,
            'phone' => $faculty->phone,
            'email' => $faculty->email,
            'photo' => $faculty->photo?->toImage($faculty->name),
            'qualification' => $faculty->qualifications[0]['title'] ?? null,
            'research_interests' => $faculty->research_interests,
            'summary' => $faculty->summary,
        ];
    }

    public static function partnerLogo(Partner $partner): array
    {
        return [
            'id' => $partner->id,
            'name' => $partner->name,
            'url' => $partner->website_url,
            'logo' => $partner->logo?->toImage($partner->name),
        ];
    }

    public static function partnerCard(Partner $partner): array
    {
        return [
            'id' => $partner->id,
            'name' => $partner->name,
            'summary' => $partner->summary,
            'highlight' => $partner->highlight,
            'url' => $partner->publicPath(),
            'image' => $partner->image?->toImage(),
        ];
    }

    public static function postCard(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'url' => $post->externalLink() ?: $post->publicPath(),
            'external' => (bool) $post->externalLink(),
            'type' => Post::Types[$post->type]['label'] ?? 'News',
            'excerpt' => $post->excerpt,
            'category' => $post->category?->name,
            'category_slug' => $post->category?->slug,
            'is_featured' => (bool) $post->is_featured,
            'reading_minutes' => $post->reading_minutes,
            'date' => $post->published_at?->format('F j, Y'),
            'datetime' => $post->published_at?->toIso8601String(),
            'image' => $post->image?->toImage(),
        ];
    }

    public static function gallery(Gallery $gallery): array
    {
        return [
            'id' => $gallery->id,
            'title' => $gallery->title,
            'slug' => $gallery->slug,
            'url' => $gallery->publicPath(),
            'description' => $gallery->description,
            'virtual_tour_url' => $gallery->virtual_tour_url,
            'tour_embeddable' => $gallery->virtual_tour_url !== null
                && str_starts_with($gallery->virtual_tour_url, 'https://')
                && in_array(parse_url($gallery->virtual_tour_url, PHP_URL_HOST), config('namtech.embed_hosts'), true),
            'cover' => $gallery->cover?->toImage(),
            'items' => $gallery->items->map(fn (GalleryItem $item): array => [
                'id' => $item->id,
                'type' => $item->type,
                'video_url' => $item->video_url,
                'caption' => $item->caption,
                'image' => $item->media?->toImage($item->caption),
            ])->filter(fn (array $item) => $item['image'] !== null)->values()->all(),
        ];
    }

    public static function leaderCard(Leader $leader): array
    {
        return [
            'id' => $leader->id,
            'name' => $leader->name,
            'url' => $leader->publicPath(),
            'designation' => $leader->designation,
            'summary' => $leader->summary,
            'email' => $leader->email,
            'photo' => $leader->photo?->toImage($leader->name),
        ];
    }

    public static function eventCard(Event $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'url' => $event->publicPath(),
            'category' => Event::Categories[$event->category] ?? $event->category,
            'subtitle' => $event->subtitle,
            'summary' => $event->summary,
            'date' => $event->starts_at?->format('F j, Y'),
            'datetime' => $event->starts_at?->toIso8601String(),
            'time' => $event->starts_at ? $event->starts_at->format('g:i A').($event->ends_at ? ' – '.$event->ends_at->format('g:i A') : '') : null,
            'venue' => $event->venue,
            'registration_url' => $event->registration_url,
            'image' => $event->image?->toImage(),
        ];
    }

    public static function publicationCard(Publication $publication): array
    {
        return [
            'id' => $publication->id,
            'title' => $publication->title,
            'url' => $publication->publicPath(),
            'external_url' => $publication->url,
            'authors' => $publication->authors,
            'publisher' => $publication->publisher,
            'date' => $publication->published_on?->format('j M Y'),
            'school' => $publication->school?->name,
            'topic' => $publication->topic,
            'summary' => $publication->summary,
            'image' => $publication->image?->toImage(),
        ];
    }

    public static function jobCard(JobOpening $job): array
    {
        return [
            'id' => $job->id,
            'title' => $job->title,
            'url' => $job->publicPath(),
            'category' => $job->category,
            'department' => $job->department,
            'closes' => $job->closes_on?->format('F j, Y'),
            'mode' => $job->mode,
            'summary' => $job->summary,
        ];
    }
}
