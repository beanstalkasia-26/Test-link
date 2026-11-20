<?php

namespace App\Http\Controllers\Admin;

use App\Admin\ResourceRegistry;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Services\ContentWorkflow;
use App\Services\PublicRenderer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreviewController extends Controller
{
    public function __invoke(Request $request, string $resource, int $id, ContentWorkflow $workflow, PublicRenderer $renderer): Response
    {
        abort_unless($request->user()->can(Permission::ContentEdit), 403);
        $definition = ResourceRegistry::get($resource);
        abort_unless($definition->isPublishable(), 404);

        $model = $workflow->previewModel($definition->model()::query()->findOrFail($id));

        return $renderer->render($model, preview: true);
    }
}
