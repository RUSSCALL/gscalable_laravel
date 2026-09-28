@if (config('services.turnstile.site_key'))
    <div class="gst_login_form_group">
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
        @error('cf-turnstile-response')
            <span class="gst-form-error-message">
                <strong>{{$message}}</strong>
            </span>
        @enderror
    </div>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
