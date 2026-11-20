<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Responses\PageResponse;
use App\Jobs\SendEnquiryNotification;
use App\Models\Enquiry;
use App\Support\AuditLogger;
use App\Support\CsvExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnquiryController extends Controller
{
    public function index(Request $request): Response
    {
        return PageResponse::render('admin/enquiries/Index', [
            'enquiries' => $this->query($request)->with('program:id,title')->latest('id')->paginate(25)->withQueryString()
                ->through(fn (Enquiry $e) => [
                    'id' => $e->id,
                    'name' => $e->name,
                    'email' => $e->email,
                    'type' => Enquiry::Types[$e->type] ?? $e->type,
                    'program' => $e->program?->title,
                    'subject' => $e->subject,
                    'status' => $e->status,
                    'notification_failed' => $e->notification_error !== null,
                    'created_at' => $e->created_at->format('d M Y, H:i'),
                ]),
            'filters' => $request->only('q', 'type', 'status', 'from', 'to'),
            'types' => Enquiry::Types,
            'statuses' => Enquiry::Statuses,
            'canExport' => $request->user()->can(Permission::EnquiriesExport),
        ]);
    }

    public function show(Enquiry $enquiry): Response
    {
        $enquiry->load('program:id,title');

        return PageResponse::render('admin/enquiries/Show', [
            'enquiry' => [
                ...$enquiry->only('id', 'name', 'email', 'phone', 'organisation', 'subject', 'message', 'status', 'consent', 'consent_text', 'source_url', 'notification_error'),
                'type' => Enquiry::Types[$enquiry->type] ?? $enquiry->type,
                'program' => $enquiry->program?->title,
                'created_at' => $enquiry->created_at->toDayDateTimeString(),
                'notified_at' => $enquiry->notified_at?->toDayDateTimeString(),
            ],
            'statuses' => Enquiry::Statuses,
        ]);
    }

    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::in(array_keys(Enquiry::Statuses))]]);
        $enquiry->update($validated);
        AuditLogger::log('enquiry.updated', null, 'Marked enquiry #'.$enquiry->id.' as '.Enquiry::Statuses[$validated['status']]);

        return back()->with('success', 'Status updated.');
    }

    public function resend(Enquiry $enquiry): RedirectResponse
    {
        SendEnquiryNotification::dispatch($enquiry);

        return back()->with('success', 'Notification queued for sending again.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->can(Permission::EnquiriesExport), 403);
        AuditLogger::log('enquiry.exported', null, 'Exported enquiries to CSV', $request->only('q', 'type', 'status', 'from', 'to'));

        return CsvExporter::download('enquiries-'.now()->format('Y-m-d').'.csv',
            ['ID', 'Received', 'Type', 'Program', 'Name', 'Email', 'Phone', 'Organisation', 'Subject', 'Message', 'Status', 'Consent', 'Source'],
            $this->query($request)->with('program:id,title')->orderBy('id')->lazy(500)->map(fn (Enquiry $e) => [
                $e->id, $e->created_at->toDateTimeString(), $e->type, $e->program?->title, $e->name, $e->email, $e->phone,
                $e->organisation, $e->subject, $e->message, $e->status, $e->consent ? 'yes' : 'no', $e->source_url,
            ]),
        );
    }

    private function query(Request $request): Builder
    {
        $search = trim((string) $request->query('q', ''));

        return Enquiry::query()
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")->orWhere('message', 'like', "%{$search}%")))
            ->when($request->query('type'), fn (Builder $q, $type) => $q->where('type', $type))
            ->when($request->query('status'), fn (Builder $q, $status) => $q->where('status', $status))
            ->when($request->query('from'), fn (Builder $q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn (Builder $q, $to) => $q->whereDate('created_at', '<=', $to));
    }
}
