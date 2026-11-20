<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Jobs\SendEnquiryNotification;
use App\Jobs\SendJobApplicationNotification;
use App\Models\Enquiry;
use App\Models\JobApplication;
use App\Models\Media;
use App\Models\NewsletterSubscriber;
use App\Models\Program;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FormController extends Controller
{
    public function enquiry(Request $request): RedirectResponse
    {
        $this->guardAgainstBots($request);

        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(Enquiry::Types))],
            'program_id' => ['nullable', 'required_if:type,program,brochure', 'integer', Rule::exists('programs', 'id')->where('status', 'published')],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-\s]{6,40}$/'],
            'organisation' => ['nullable', 'string', 'max:160'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required_unless:type,case_study,brochure', 'nullable', 'string', 'min:10', 'max:5000'],
            'grade' => ['nullable', 'required_if:type,brochure', 'string', 'max:80'],
            'city' => ['nullable', 'required_if:type,brochure', 'string', 'max:80'],
            'consent' => ['accepted'],
        ], [
            'program_id.required_if' => 'Please choose the program you are interested in.',
            'phone.regex' => 'Please enter a valid phone number.',
            'consent.accepted' => 'Please confirm that we may contact you about your enquiry.',
            'message.min' => 'Please tell us a little more (at least 10 characters).',
            'message.required_unless' => 'Please tell us about your enquiry.',
            'grade.required_if' => 'Please enter your grade or class.',
            'city.required_if' => 'Please enter your city.',
        ]);

        $enquiry = Enquiry::create([
            ...collect($validated)->except('consent', 'grade', 'city')->all(),
            'message' => $validated['message'] ?? match ($validated['type']) {
                'case_study' => 'Requested the full case study: '.Str::limit((string) $request->headers->get('referer'), 300),
                'brochure' => 'Downloaded the program brochure. Grade/class: '.$validated['grade'].'. City: '.$validated['city'].'.',
                default => '',
            },
            'consent' => true,
            'consent_text' => Str::limit((string) Setting::get('enquiry_consent', 'I agree to be contacted by NAMTECH about my enquiry.'), 490),
            'source_url' => Str::limit((string) $request->headers->get('referer'), 2000, ''),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 490, ''),
            'status' => 'new',
        ]);

        SendEnquiryNotification::dispatch($enquiry);

        if ($enquiry->type === 'brochure') {
            $brochure = Program::query()->find($enquiry->program_id)?->details['brochure']['file'] ?? null;
            $document = $brochure ? Media::query()->find($brochure) : null;
            $download = $document?->isDocument() ? $document->documentUrl() : null;

            return back()->with('form_success', [
                'form' => 'enquiry',
                'message' => $download
                    ? 'Thank you, '.$enquiry->name.'. Your brochure is ready.'
                    : 'Thank you, '.$enquiry->name.'. Our team will email you the brochure shortly.',
                'download' => $download,
            ]);
        }

        return back()->with('form_success', [
            'form' => 'enquiry',
            'message' => 'Thank you, '.$enquiry->name.'. Your enquiry has been received and our team will respond by email.',
        ]);
    }

    public function jobApplication(Request $request): RedirectResponse
    {
        $this->guardAgainstBots($request);

        $validated = $request->validate([
            'job_opening_id' => ['required', 'integer', Rule::exists('jobs_openings', 'id')->where('status', 'published')],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-\s]{6,40}$/'],
            'qualification' => ['nullable', 'string', 'max:120'],
            'qualification_year' => ['nullable', 'string', 'max:10'],
            'institution' => ['nullable', 'string', 'max:200'],
            'experience_years' => ['nullable', 'string', 'max:20'],
            'current_company' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:120'],
            'resume' => ['required', 'file', 'max:5120', 'mimes:pdf,doc,docx', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'consent' => ['accepted'],
        ], [
            'resume.required' => 'Please attach your CV.',
            'resume.mimes' => 'Your CV must be a PDF or Word document.',
            'resume.max' => 'Your CV must be smaller than 5 MB.',
            'consent.accepted' => 'Please confirm that NAMTECH may process your application.',
        ]);

        $file = $request->file('resume');
        $path = $file->storeAs('applications/'.now()->format('Y/m'), Str::uuid().'.'.$file->extension(), 'local');

        $application = JobApplication::create([
            ...collect($validated)->except(['resume', 'consent'])->all(),
            'resume_path' => $path,
            'resume_name' => Str::limit($file->getClientOriginalName(), 200, ''),
            'consent' => true,
            'status' => 'new',
            'ip_address' => $request->ip(),
        ]);

        SendJobApplicationNotification::dispatch($application);

        return back()->with('form_success', ['form' => 'job', 'message' => 'Thank you for your interest in working with NAMTECH. Our Talent Acquisition Team will review your application and be in touch shortly.']);
    }

    public function newsletter(Request $request): RedirectResponse
    {
        $this->guardAgainstBots($request, 'newsletter');

        $validated = $request->validate([
            'newsletter_email' => ['required', 'email:rfc', 'max:255'],
            'newsletter_consent' => ['accepted'],
        ], [
            'newsletter_email.required' => 'Please enter your email address.',
            'newsletter_email.email' => 'Please enter a valid email address.',
            'newsletter_consent.accepted' => 'Please agree to receive the newsletter.',
        ]);

        $email = Str::lower($validated['newsletter_email']);
        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => $email]);
        $subscriber->fill([
            'status' => 'subscribed',
            'consented_at' => now(),
            'consent_text' => Str::limit((string) Setting::get('newsletter_consent', 'I agree to receive email updates from NAMTECH.'), 490),
            'source_url' => Str::limit((string) $request->headers->get('referer'), 2000, ''),
            'ip_address' => $request->ip(),
            'unsubscribed_at' => null,
        ]);
        $subscriber->unsubscribe_token ??= Str::random(48);
        $subscriber->save();

        return back()->with('form_success', ['form' => 'newsletter', 'message' => 'Thank you for subscribing to the NAMTECH newsletter.']);
    }

    public function unsubscribe(string $token): RedirectResponse
    {
        NewsletterSubscriber::query()->where('unsubscribe_token', $token)->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);

        return redirect('/')->with('form_success', ['form' => 'newsletter', 'message' => 'You have been unsubscribed from the NAMTECH newsletter.']);
    }

    private function guardAgainstBots(Request $request, string $prefix = ''): void
    {
        $field = $prefix ? $prefix.'_website' : 'website';
        $elapsedMs = (int) $request->input($prefix ? $prefix.'_elapsed' : 'elapsed', 0);

        if (filled($request->input($field)) || ($elapsedMs > 0 && $elapsedMs < 2000)) {
            throw ValidationException::withMessages([($prefix ? $prefix.'_email' : 'message') => 'Your submission could not be accepted. Please try again.']);
        }
    }
}
