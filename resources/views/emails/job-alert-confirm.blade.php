@component('mail::message')
# Confirm your job alerts

Someone &mdash; we hope you &mdash; asked us to send an email whenever a new role
opens up at Global Scalable Technologies.

**You asked about:** {{ $target }}

Confirm the address below and we'll start sending them. Until you do, we won't
email you about roles at all.

@component('mail::button', ['url' => $confirmUrl, 'color' => 'primary'])
Confirm my alerts
@endcomponent

If you didn't request this, you can safely ignore this message &mdash; nothing
further will be sent to this address.

Best regards,
The Recruitment Team

@component('mail::subcopy')
If the button doesn't work, paste this link into your browser:
[{{ $confirmUrl }}]({{ $confirmUrl }})
@endcomponent
@endcomponent
