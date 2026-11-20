<?php

namespace App\Http\Controllers\Admin;

use App\Admin\ResourceRegistry;
use App\Admin\Resources\Resource;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Responses\PageResponse;
use App\Models\ContentRevision;
use App\Models\Media;
use App\Sections\SectionRegistry;
use App\Services\ContentWorkflow;
use App\Support\AuditLogger;
use App\Support\Fields;
use App\Support\OptionSources;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ResourceController extends Controller
{
    public function __construct(private ContentWorkflow $workflow) {}

    public function index(Request $request, string $resource): Response
    {
        $definition = $this->resolve($request, $resource);
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        [$sortColumn, $sortDirection] = $definition->defaultSort();

        $records = $definition->query()
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($definition, $search) {
                foreach ($definition->searchable() as $column) {
                    $query->orWhere($column, 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%');
                }
            }))
            ->when($definition->isPublishable() && $status !== '', function (Builder $query) use ($status) {
                match ($status) {
                    'review' => $query->where('review_state', 'pending'),
                    'pending' => $query->whereHas('workingCopy'),
                    default => $query->where('status', $status),
                };
            })
            ->orderBy($sortColumn, $sortDirection)
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Model $record) => $definition->row($record));

        return PageResponse::render('admin/resources/Index', [
            'resource' => $definition->meta(),
            'records' => $records,
            'filters' => ['q' => $search, 'status' => $status],
            'can' => $this->abilities($request, $definition),
        ]);
    }

    public function create(Request $request, string $resource): Response
    {
        $definition = $this->resolve($request, $resource);

        return $this->form($request, $definition, null, $definition->defaults());
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $definition = $this->resolve($request, $resource);
        [$attributes, $relations, $media] = $this->validated($request, $definition, null);

        $record = $definition->newModel();
        if ($definition->isPublishable()) {
            $this->workflow->save($record, $attributes, $relations, $media);
        } else {
            $record->fill($attributes)->save();
            AuditLogger::log('content.created', $record, 'Created '.$definition->singular().' "'.$this->title($definition, $record).'"');
        }

        return to_route('admin.resources.edit', [$resource, $record->getKey()])->with('success', $definition->singular().' created.');
    }

    public function edit(Request $request, string $resource, int $id): Response
    {
        $definition = $this->resolve($request, $resource);
        $record = $this->find($definition, $id);

        return $this->form($request, $definition, $record, $definition->formValues($record));
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $definition = $this->resolve($request, $resource);
        $record = $this->find($definition, $id);
        [$attributes, $relations, $media] = $this->validated($request, $definition, $record);

        if ($definition->isPublishable()) {
            $held = $this->workflow->save($record, $attributes, $relations, $media);
            $message = $held
                ? 'Changes saved as a draft. The live page is unchanged until the changes are published.'
                : 'Changes saved.';
        } else {
            $record->fill($attributes)->save();
            AuditLogger::log('content.updated', $record, 'Updated '.$definition->singular().' "'.$this->title($definition, $record).'"');
            $message = 'Changes saved.';
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, string $resource, int $id): RedirectResponse
    {
        $definition = $this->resolve($request, $resource);
        $this->authorizePermission($request, $definition->isPublishable() ? Permission::ContentDelete : $definition->permission());
        $record = $this->find($definition, $id);

        if ($definition->isPublishable() && $record->isLive()) {
            return back()->with('error', 'Unpublish this item before deleting it.');
        }

        $title = $this->title($definition, $record);
        DB::transaction(fn () => $record->delete());
        AuditLogger::log('content.deleted', null, 'Deleted '.$definition->singular().' "'.$title.'"', ['type' => $resource, 'id' => $id]);

        return to_route('admin.resources.index', $resource)->with('success', $definition->singular().' deleted.');
    }

    public function submit(Request $request, string $resource, int $id): RedirectResponse
    {
        $definition = $this->resolvePublishable($request, $resource, Permission::ContentSubmit);
        $this->workflow->submitForReview($this->find($definition, $id));

        return back()->with('success', 'Submitted for review. Publishers have been notified on their dashboard.');
    }

    public function publish(Request $request, string $resource, int $id): RedirectResponse
    {
        $definition = $this->resolvePublishable($request, $resource, Permission::ContentPublish);
        $this->workflow->publish($this->find($definition, $id));

        return back()->with('success', 'Published. The live site has been updated.');
    }

    public function schedule(Request $request, string $resource, int $id): RedirectResponse
    {
        $definition = $this->resolvePublishable($request, $resource, Permission::ContentPublish);
        $validated = $request->validate(['scheduled_for' => ['required', 'date', 'after:now']]);
        $when = Carbon::parse($validated['scheduled_for'], config('app.timezone'));
        $this->workflow->schedule($this->find($definition, $id), $when);

        return back()->with('success', 'Scheduled for '.$when->toDayDateTimeString().'.');
    }

    public function unpublish(Request $request, string $resource, int $id): RedirectResponse
    {
        $definition = $this->resolvePublishable($request, $resource, Permission::ContentPublish);
        $this->workflow->unpublish($this->find($definition, $id));

        return back()->with('success', 'Unpublished. The item is no longer visible on the site.');
    }

    public function discard(Request $request, string $resource, int $id): RedirectResponse
    {
        $definition = $this->resolvePublishable($request, $resource, Permission::ContentEdit);
        $this->workflow->discardWorkingCopy($this->find($definition, $id));

        return back()->with('success', 'Pending changes discarded.');
    }

    public function restore(Request $request, string $resource, int $id, int $revision): RedirectResponse
    {
        $definition = $this->resolvePublishable($request, $resource, Permission::ContentEdit);
        $record = $this->find($definition, $id);
        $revisionModel = ContentRevision::query()
            ->where('revisionable_type', $record->getMorphClass())
            ->where('revisionable_id', $record->getKey())
            ->findOrFail($revision);

        $held = $this->workflow->restore($record, $revisionModel);

        return back()->with('success', $held ? 'Revision restored as pending changes. Publish to make it live.' : 'Revision restored.');
    }

    private function form(Request $request, Resource $definition, ?Model $record, array $values): Response
    {
        $isPublishable = $definition->isPublishable();

        return PageResponse::render('admin/resources/Form', [
            'resource' => $definition->meta(),
            'fields' => Fields::forAdmin($definition->allFields()),
            'sectionTypes' => collect($definition->allFields())->contains('type', 'sections') ? SectionRegistry::forAdmin() : [],
            'record' => $record ? [
                'id' => $record->getKey(),
                'title' => $this->title($definition, $record),
                'status' => $isPublishable ? $record->status->value : null,
                'status_label' => $isPublishable ? $record->status->label() : null,
                'review_state' => $isPublishable ? $record->review_state : null,
                'scheduled_for' => $isPublishable ? $record->scheduled_for?->toDayDateTimeString() : null,
                'published_at' => $isPublishable ? $record->published_at?->toDayDateTimeString() : null,
                'has_pending_changes' => $isPublishable && $record->workingCopy()->exists(),
                'public_url' => $isPublishable && $record->isLive() ? $record->publicPath() : null,
                'preview_url' => $isPublishable && $record->publicPath() !== null ? route('admin.preview', [$definition->slug(), $record->getKey()]) : null,
                'updated_at' => $record->updated_at?->toDayDateTimeString(),
                'updated_by' => $isPublishable ? $record->updater?->name : null,
            ] : null,
            'values' => $values,
            'media' => $this->mediaPreviews($definition, $values),
            'revisions' => $record ? $definition->revisions($record) : [],
            'can' => $this->abilities($request, $definition),
        ]);
    }

    private function mediaPreviews(Resource $definition, array $values): array
    {
        $ids = Fields::mediaIds($definition->allFields(), $values);
        foreach ((array) ($values['sections'] ?? []) as $section) {
            if (is_array($section) && isset($section['type'])) {
                $ids = array_merge($ids, SectionRegistry::mediaIds($section['type'], $section['data'] ?? []));
            }
        }

        return Media::query()->whereIn('id', array_unique($ids))->get()
            ->mapWithKeys(fn ($media) => [$media->id => $media->toAdmin()])->all();
    }

    private function validated(Request $request, Resource $definition, ?Model $record): array
    {
        OptionSources::forget();
        $validated = $request->validate($definition->rules($request, $record), $definition->messages());

        return $definition->extract($validated, $record);
    }

    private function resolve(Request $request, string $resource): Resource
    {
        $definition = ResourceRegistry::get($resource);
        $this->authorizePermission($request, $definition->permission());

        return $definition;
    }

    private function resolvePublishable(Request $request, string $resource, string $permission): Resource
    {
        $definition = $this->resolve($request, $resource);
        abort_unless($definition->isPublishable(), 404);
        $this->authorizePermission($request, $permission);

        return $definition;
    }

    private function find(Resource $definition, int $id): Model
    {
        return $definition->model()::query()->findOrFail($id);
    }

    private function authorizePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()->can($permission), 403, 'You do not have permission to do that.');
    }

    private function title(Resource $definition, Model $record): string
    {
        return (string) $record->getAttribute($definition->titleAttribute());
    }

    private function abilities(Request $request, Resource $definition): array
    {
        $user = $request->user();

        return [
            'edit' => $user->can($definition->permission()),
            'submit' => $user->can(Permission::ContentSubmit),
            'publish' => $user->can(Permission::ContentPublish),
            'delete' => $definition->isPublishable() ? $user->can(Permission::ContentDelete) : $user->can($definition->permission()),
        ];
    }
}
