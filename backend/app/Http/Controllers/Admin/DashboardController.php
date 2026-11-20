<?php

namespace App\Http\Controllers\Admin;

use App\Admin\ResourceRegistry;
use App\Enums\ContentStatus;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Responses\PageResponse;
use App\Models\AuditLog;
use App\Models\Enquiry;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $publishable = collect(ResourceRegistry::all())->filter->isPublishable();

        $awaitingReview = $publishable->flatMap(fn ($resource) => $resource->model()::query()
            ->with('updater:id,name')->where('review_state', 'pending')->latest('updated_at')->limit(10)->get()
            ->map(fn (Model $model) => $this->item($resource, $model)))
            ->sortByDesc('updated_at_raw')->take(10)->values();

        $scheduled = $publishable->flatMap(fn ($resource) => $resource->model()::query()
            ->with('updater:id,name')->whereNotNull('scheduled_for')->orderBy('scheduled_for')->limit(10)->get()
            ->map(fn (Model $model) => $this->item($resource, $model) + ['scheduled_for' => $model->scheduled_for->toDayDateTimeString()]))
            ->sortBy('scheduled_for')->take(10)->values();

        $drafts = $publishable->flatMap(fn ($resource) => $resource->model()::query()
            ->with('updater:id,name')->where('status', ContentStatus::Draft)->latest('updated_at')->limit(5)->get()
            ->map(fn (Model $model) => $this->item($resource, $model)))
            ->sortByDesc('updated_at_raw')->take(8)->values();

        $canSeeLeads = $user->can(Permission::EnquiriesView);

        return PageResponse::render('admin/Dashboard', [
            'counts' => [
                'published_pages' => Page::query()->published()->count(),
                'draft_items' => $publishable->sum(fn ($resource) => $resource->model()::query()->where('status', ContentStatus::Draft)->count()),
                'awaiting_review' => $publishable->sum(fn ($resource) => $resource->model()::query()->where('review_state', 'pending')->count()),
                'scheduled' => $publishable->sum(fn ($resource) => $resource->model()::query()->whereNotNull('scheduled_for')->count()),
                'new_enquiries' => $canSeeLeads ? Enquiry::query()->where('status', 'new')->count() : null,
                'subscribers_30d' => $canSeeLeads ? NewsletterSubscriber::query()->where('status', 'subscribed')->where('created_at', '>=', now()->subDays(30))->count() : null,
                'subscribers_total' => $canSeeLeads ? NewsletterSubscriber::query()->where('status', 'subscribed')->count() : null,
            ],
            'awaitingReview' => $awaitingReview,
            'scheduled' => $scheduled,
            'drafts' => $drafts,
            'enquiries' => $canSeeLeads ? Enquiry::query()->latest()->limit(6)->get(['id', 'name', 'type', 'subject', 'status', 'created_at', 'notification_error'])
                ->map(fn (Enquiry $e) => [
                    'id' => $e->id, 'name' => $e->name, 'type' => Enquiry::Types[$e->type] ?? $e->type, 'subject' => $e->subject,
                    'status' => $e->status, 'created_at' => $e->created_at->diffForHumans(), 'notification_failed' => $e->notification_error !== null,
                ]) : null,
            'activity' => $user->can(Permission::AuditView)
                ? AuditLog::query()->with('user:id,name')->latest('id')->limit(10)->get()->map(fn (AuditLog $log) => [
                    'id' => $log->id, 'description' => $log->description, 'user' => $log->user?->name ?? 'System', 'created_at' => $log->created_at?->diffForHumans(),
                ])
                : AuditLog::query()->where('user_id', $user->id)->latest('id')->limit(10)->get()->map(fn (AuditLog $log) => [
                    'id' => $log->id, 'description' => $log->description, 'user' => 'You', 'created_at' => $log->created_at?->diffForHumans(),
                ]),
        ]);
    }

    private function item($resource, Model $model): array
    {
        return [
            'key' => $resource->slug().'-'.$model->getKey(),
            'title' => (string) $model->getAttribute($resource->titleAttribute()),
            'type' => $resource->singular(),
            'url' => route('admin.resources.edit', [$resource->slug(), $model->getKey()]),
            'updated_at' => $model->updated_at?->diffForHumans(),
            'updated_at_raw' => $model->updated_at?->timestamp,
            'updated_by' => $model->updater?->name,
        ];
    }
}
