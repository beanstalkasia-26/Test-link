<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Responses\PageResponse;
use App\Models\NewsletterSubscriber;
use App\Support\AuditLogger;
use App\Support\CsvExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends Controller
{
    public function index(Request $request): Response
    {
        return PageResponse::render('admin/newsletter/Index', [
            'subscribers' => $this->query($request)->latest('id')->paginate(30)->withQueryString()->through(fn (NewsletterSubscriber $s) => [
                'id' => $s->id,
                'email' => $s->email,
                'status' => $s->status,
                'source_url' => $s->source_url,
                'created_at' => $s->created_at->format('d M Y'),
            ]),
            'filters' => $request->only('q', 'status'),
            'canExport' => $request->user()->can(Permission::EnquiriesExport),
        ]);
    }

    public function unsubscribe(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
        AuditLogger::log('newsletter.unsubscribed', null, 'Unsubscribed newsletter subscriber #'.$subscriber->id);

        return back()->with('success', 'Subscriber unsubscribed.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->can(Permission::EnquiriesExport), 403);
        AuditLogger::log('newsletter.exported', null, 'Exported newsletter subscribers to CSV');

        return CsvExporter::download('newsletter-subscribers-'.now()->format('Y-m-d').'.csv',
            ['Email', 'Status', 'Subscribed', 'Consent text', 'Source'],
            $this->query($request)->orderBy('id')->lazy(500)->map(fn (NewsletterSubscriber $s) => [
                $s->email, $s->status, $s->created_at->toDateTimeString(), $s->consent_text, $s->source_url,
            ]),
        );
    }

    private function query(Request $request): Builder
    {
        return NewsletterSubscriber::query()
            ->when(trim((string) $request->query('q')), fn (Builder $q, $search) => $q->where('email', 'like', "%{$search}%"))
            ->when($request->query('status'), fn (Builder $q, $status) => $q->where('status', $status));
    }
}
