<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\PageResponse;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));

        return PageResponse::render('admin/applications/Index', [
            'applications' => JobApplication::query()->with('job:id,title')
                ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
                ->when($request->query('job'), fn ($q, $job) => $q->where('job_opening_id', $job))
                ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
                ->latest('id')->paginate(25)->withQueryString()
                ->through(fn (JobApplication $a) => [
                    'id' => $a->id,
                    'name' => $a->first_name.' '.$a->last_name,
                    'email' => $a->email,
                    'job' => $a->job?->title,
                    'status' => $a->status,
                    'created_at' => $a->created_at->format('d M Y, H:i'),
                ]),
            'jobs' => JobOpening::query()->orderBy('title')->pluck('title', 'id'),
            'statuses' => JobApplication::Statuses,
            'filters' => $request->only('q', 'job', 'status'),
        ]);
    }

    public function show(JobApplication $application): Response
    {
        $application->load('job:id,title');

        return PageResponse::render('admin/applications/Show', [
            'application' => [
                ...$application->only('id', 'first_name', 'last_name', 'email', 'phone', 'qualification', 'qualification_year', 'institution',
                    'experience_years', 'current_company', 'location', 'resume_name', 'status', 'notification_error'),
                'job' => $application->job?->title,
                'created_at' => $application->created_at->toDayDateTimeString(),
                'has_resume' => $application->resume_path !== null,
            ],
            'statuses' => JobApplication::Statuses,
        ]);
    }

    public function update(Request $request, JobApplication $application): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::in(array_keys(JobApplication::Statuses))]]);
        $application->update($validated);
        AuditLogger::log('application.updated', null, 'Marked job application #'.$application->id.' as '.JobApplication::Statuses[$validated['status']]);

        return back()->with('success', 'Status updated.');
    }

    public function resume(JobApplication $application): StreamedResponse
    {
        abort_unless($application->resume_path && Storage::disk('local')->exists($application->resume_path), 404);
        AuditLogger::log('application.resume_downloaded', null, 'Downloaded CV for job application #'.$application->id);

        return Storage::disk('local')->download($application->resume_path, $application->resume_name ?: 'cv', [
            'Cache-Control' => 'no-store, private',
        ]);
    }
}
