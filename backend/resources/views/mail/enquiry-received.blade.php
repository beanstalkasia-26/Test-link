<x-mail::message>
# New enquiry received

**Type:** {{ \App\Models\Enquiry::Types[$enquiry->type] ?? $enquiry->type }}
@if ($enquiry->program)
**Program:** {{ $enquiry->program->title }}
@endif
**Name:** {{ $enquiry->name }}
**Email:** {{ $enquiry->email }}
@if ($enquiry->phone)
**Phone:** {{ $enquiry->phone }}
@endif
@if ($enquiry->organisation)
**Organisation:** {{ $enquiry->organisation }}
@endif
@if ($enquiry->subject)
**Subject:** {{ $enquiry->subject }}
@endif

<x-mail::panel>
{{ $enquiry->message }}
</x-mail::panel>

<x-mail::button :url="$adminUrl">
View in admin
</x-mail::button>

Reply directly to this email to respond to {{ $enquiry->name }}.
</x-mail::message>
