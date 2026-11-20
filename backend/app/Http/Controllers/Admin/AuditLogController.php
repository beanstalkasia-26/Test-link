<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\PageResponse;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));

        return PageResponse::render('admin/AuditLog', [
            'logs' => AuditLog::query()->with('user:id,name')
                ->when($search !== '', fn ($q) => $q->where('description', 'like', "%{$search}%"))
                ->when($request->query('action'), fn ($q, $action) => $q->where('action', 'like', $action.'%'))
                ->latest('id')->paginate(40)->withQueryString()
                ->through(fn (AuditLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'user' => $log->user?->name ?? 'System',
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at?->format('d M Y, H:i:s'),
                ]),
            'filters' => $request->only('q', 'action'),
        ]);
    }
}
