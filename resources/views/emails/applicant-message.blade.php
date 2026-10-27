@component('mail::message')
Dear {{ $application->first_name }} {{ $application->last_name }},

{!! nl2br(e($body)) !!}

Regarding your application for **{{ $application->jobPosting->title }}** (reference {{ $application->reference }}).

Best regards,
{{ $replyToAdmin->name }}
Global Scalable Technologies

@endcomponent
