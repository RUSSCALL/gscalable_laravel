@component('mail::message')
# Your Interview Is Scheduled

Dear {{ $interview->application->first_name }} {{ $interview->application->last_name }},

We would like to invite you to an interview for the **{{ $interview->application->jobPosting->title }}** position at Global Scalable Technologies.

**When:** {{ $interview->scheduled_at->format('l, F j, Y \a\t g:i A') }} {{ $interview->timezone }} ({{ $interview->duration_minutes }} minutes)
**Format:** {{ $interview->type_label }}
@if($interview->location)
**{{ $interview->type === 'in_person' ? 'Where' : 'Link / details' }}:** {{ $interview->location }}
@endif
@if($interview->interviewers)
**With:** {{ $interview->interviewers }}
@endif

Your application reference is **{{ $interview->application->reference }}**. If the time doesn't work for you, just reply to this email.

Best regards,
The Recruitment Team

@endcomponent
