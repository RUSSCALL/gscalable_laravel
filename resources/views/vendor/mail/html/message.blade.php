<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')

{{-- Bulk mail (job alerts) needs a physical postal address to satisfy CAN-SPAM.
     Set MAIL_POSTAL_ADDRESS in .env before any alert campaign goes out. --}}
@if(config('mail.postal_address'))
{{ config('mail.postal_address') }}
@endif

[{{ config('app.url') }}]({{ config('app.url') }})
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
