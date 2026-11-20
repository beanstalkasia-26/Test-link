<?php

namespace App\Http\Controllers\Admin;

use App\Admin\ResourceRegistry;
use App\Http\Controllers\Controller;
use App\Http\Responses\PageResponse;
use App\Models\Media;
use App\Models\MediaUsage;
use App\Services\MediaUploader;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function __construct(private MediaUploader $uploader) {}

    public function index(Request $request): Response
    {
        return PageResponse::render('admin/media/Index', [
            'media' => $this->paginate($request),
            'filters' => ['q' => (string) $request->query('q', '')],
            'maxUploadKb' => config('namtech.max_upload_kb'),
        ]);
    }

    public function browse(Request $request): JsonResponse
    {
        return response()->json($this->paginate($request, 24));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:'.config('namtech.max_upload_kb'), 'mimetypes:image/jpeg,image/png,image/webp,image/avif,application/pdf'],
            'alt' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'credit' => ['nullable', 'string', 'max:255'],
        ], [
            'alt.required' => 'Describe the image for people using screen readers (alt text), or give the document a title. Use "decorative" only if an image adds no information.',
        ]);

        $media = $this->uploader->store($request->file('file'), [
            'alt' => $validated['alt'] === 'decorative' ? '' : $validated['alt'],
            'caption' => $validated['caption'] ?? null,
            'credit' => $validated['credit'] ?? null,
        ]);
        AuditLogger::log('media.uploaded', $media, 'Uploaded image "'.$media->original_name.'"');

        if (! $request->header('X-App-Visit')) {
            return response()->json($media->toAdmin(), 201);
        }

        return back()->with('success', 'Image uploaded. Optimised versions are being generated.');
    }

    public function show(Media $media): JsonResponse
    {
        return response()->json($media->toAdmin() + ['usages' => $this->usages($media)]);
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $validated = $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'credit' => ['nullable', 'string', 'max:255'],
        ]);
        $media->update($validated);
        AuditLogger::log('media.updated', $media, 'Updated details of image "'.$media->original_name.'"');

        return back()->with('success', 'Image details saved.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $usages = $this->usages($media);
        if ($usages !== []) {
            return back()->with('error', 'This image is still used in '.count($usages).' place(s). Replace it there first: '.collect($usages)->pluck('title')->implode(', '));
        }

        $name = $media->original_name;
        $this->uploader->delete($media);
        AuditLogger::log('media.deleted', null, 'Deleted image "'.$name.'"');

        return back()->with('success', 'Image deleted.');
    }

    private function usages(Media $media): array
    {
        return $media->usages()->with('usable')->get()->map(function (MediaUsage $usage): array {
            $usable = $usage->usable;
            $resource = $usable ? ResourceRegistry::forModel($usable) : null;

            return [
                'field' => $usage->field,
                'title' => $usable && method_exists($usable, 'displayTitle') ? $usable->displayTitle() : 'Deleted item',
                'type' => $resource?->singular() ?? class_basename((string) $usage->usable_type),
                'url' => $resource ? route('admin.resources.edit', [$resource->slug(), $usable->getKey()]) : null,
            ];
        })->unique(fn ($u) => $u['type'].$u['title'])->values()->all();
    }

    private function paginate(Request $request, int $perPage = 30): mixed
    {
        $search = trim((string) $request->query('q', ''));

        return Media::query()
            ->when(in_array($request->query('kind'), ['image', 'document'], true), fn ($q) => $q->where('kind', $request->query('kind')))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('original_name', 'like', "%{$search}%")->orWhere('alt', 'like', "%{$search}%")))
            ->withCount('usages')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Media $media) => $media->toAdmin() + ['usages_count' => $media->usages_count]);
    }
}
