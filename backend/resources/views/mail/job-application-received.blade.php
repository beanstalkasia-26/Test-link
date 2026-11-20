<x-mail::message>
# New job application

**Position:** {{ $application->job?->title ?? '—' }}
**Name:** {{ $application->first_name }} {{ $application->last_name }}
**Email:** {{ $application->email }}
@if ($application->phone)
**Phone:** {{ $application->phone }}
@endif
@if ($application->qualification)
**Qualification:** {{ $application->qualification }} {{ $application->qualification_year }}
@endif
@if ($application->current_company)
**Current company:** {{ $application->current_company }}
@endif

<x-mail::button :url="$adminUrl">
Review application and CV
</x-mail::button>
</x-mail::message>
