@extends('main_layout')

@section('title', 'Application received — Global Scalable Technologies')

@push('head')
<meta name="robots" content="noindex">
@endpush

@section('main_content')

<!-- ======= Confirmation Hero ======= -->
<section class="application-hero">
    <div class="container" data-aos="fade-up">
        <div class="section-title">
            <h2>Careers</h2>
            <p>Application received</p>
        </div>
        <p class="careers-hero-subtitle">
            Thanks, {{ $application['first_name'] }} — your application for
            <strong>{{ $job->title }}</strong> is in.
        </p>
    </div>
</section>

<!-- ======= Confirmation Body ======= -->
<section class="application-band">
    <div class="container" data-aos="fade-up">
        <div class="row">
            <div class="col-lg-7">
                <div class="confirm-reference gst-card-base">
                    <p class="confirm-reference-label">Your reference</p>
                    <p class="confirm-reference-value">{{ $application['reference'] }}</p>
                    <p class="confirm-reference-note">
                        Quote this if you get in touch about your application. We've emailed a copy
                        to {{ $application['email'] }}.
                    </p>
                </div>

                <div class="application-aside-card gst-card-base">
                    <h2>What happens next</h2>
                    <ol class="application-next-steps">
                        <li>Our recruiting team reviews your application against the role.</li>
                        <li>If there's a fit, we'll reach out to arrange a first conversation.</li>
                        <li>You'll hear from us either way — we don't leave applications unanswered.</li>
                    </ol>
                </div>

                @if($canClaim)
                    <div class="claim-card gst-card-base">
                        <h2>Want to track this application?</h2>
                        <p>
                            Create an account with the email you just applied with and you'll be able
                            to check its status any time. Entirely optional — your application is
                            already submitted either way.
                        </p>

                        <form method="POST" action="{{ route('careers.claim') }}" class="claim-form">
                            @csrf
                            <input type="hidden" name="email" value="{{ $application['email'] }}">

                            <div class="claim-field">
                                <label for="claim_email">Email</label>
                                <input type="email" id="claim_email" value="{{ $application['email'] }}"
                                       disabled autocomplete="username">
                            </div>

                            <div class="claim-field">
                                <label for="password">Choose a password <span class="required-mark">*</span></label>
                                <input type="password" id="password" name="password" required
                                       autocomplete="new-password"
                                       @class(['is-invalid' => $errors->has('password')])>
                                @error('password') <p class="field-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="claim-field">
                                <label for="password_confirmation">Confirm password <span class="required-mark">*</span></label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       required autocomplete="new-password">
                            </div>

                            <p class="field-hint">
                                We'll email you a link to confirm your address before you can sign in.
                            </p>

                            <div class="claim-actions">
                                <button type="submit" class="btn-gst-primary">
                                    <i class="bi bi-person-check"></i> Create account
                                </button>
                                <a href="{{ route('careers') }}" class="claim-decline">No thanks, I'm done</a>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="claim-card gst-card-base">
                        <h2>Track your applications</h2>
                        @auth
                            <p>This application is linked to your account.</p>
                        @else
                            <p>
                                You already have an account with this email address. Sign in to see
                                this application alongside your others.
                            </p>
                            <a href="{{ route('login') }}" class="btn-gst-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Sign in
                            </a>
                        @endauth
                    </div>
                @endif
            </div>

            <div class="col-lg-5">
                <div class="application-aside">
                    <div class="application-aside-card gst-card-base">
                        <h2>{{ $job->title }}</h2>
                        <ul class="job-facts" style="margin-top: 0; padding-top: 0; border-top: none;">
                            <li>
                                <span class="job-fact-label">Type</span>
                                <span class="job-fact-value">{{ $job->employment_type }}</span>
                            </li>
                            <li>
                                <span class="job-fact-label">Level</span>
                                <span class="job-fact-value">{{ $job->experience_level }}</span>
                            </li>
                            <li>
                                <span class="job-fact-label">Applied</span>
                                <span class="job-fact-value">{{ now()->format('M j, Y') }}</span>
                            </li>
                        </ul>
                        <a href="{{ route('careers.show', $job->slug) }}" class="btn-gst-outline"
                           style="width: 100%; margin-top: 18px;">
                            <i class="bi bi-eye"></i> View the role
                        </a>
                    </div>

                    <div class="application-aside-card gst-card-base">
                        <h2>Keep looking</h2>
                        <p style="font-family: var(--gst-font-body); font-size: 14px; line-height: 1.6; color: var(--gst-slate-500); margin: 0 0 16px;">
                            You can apply to as many roles as you like.
                        </p>
                        <a href="{{ route('careers') }}" class="btn-gst-outline" style="width: 100%;">
                            <i class="bi bi-arrow-left"></i> Browse open roles
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><!-- End Confirmation -->

@endsection
