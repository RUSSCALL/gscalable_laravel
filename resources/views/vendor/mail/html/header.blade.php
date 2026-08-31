@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
{{-- The stock component only rendered the logo when the slot was literally
     "Laravel", so it never appeared. The logo is white-on-transparent and the
     header band is navy, so it reads; the app name is the alt text for clients
     that block images. --}}
<img src="{{ asset('assets/img/GST-logo-white.png') }}" class="logo" alt="{{ trim($slot) }}">
</a>
</td>
</tr>
