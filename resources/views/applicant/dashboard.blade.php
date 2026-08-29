@extends('main_layout')

@section('title', 'Your applications — Global Scalable Technologies')

@push('head')
<meta name="robots" content="noindex">
@endpush

@section('main_content')

<!-- ======= Dashboard Hero ======= -->
<section class="application-hero">
    <div class="container" data-aos="fade-up">
        <div class="section-title">
            <h2>Your account</h2>
            <p>Your applications</p>
        </div>
        <p class="careers-hero-subtitle">
            @if($totalCount > 0)
                Tracking {{ $totalCount }} {{ Str::plural('application', $totalCount) }}.
                We'll email you whenever something changes.
            @else
                Applications you submit will appear here.
            @endif
        </p>
    </div>
</section>

<!-- ======= Dashboard Body ======= -->
<section class="application-band">
    <div class="container" data-aos="fade-up">

        @if($totalCount === 0)
            <div class="careers-empty gst-card-base">
                <h3>You haven't applied to anything yet</h3>
                <p>When you apply for a role, you'll be able to follow its progress here.</p>
                <a href="{{ route('careers') }}" class="btn-gst-primary">
                    <i class="bi bi-search"></i> Browse open roles
                </a>
            </div>
        @else
            @if($activeApplications->isNotEmpty())
                <h2 class="dashboard-group-heading">In progress</h2>
                <div class="dashboard-list">
                    @foreach($activeApplications as $application)
                        @include('partials.application-row', ['application' => $application])
                    @endforeach
                </div>
            @endif

            @if($closedApplications->isNotEmpty())
                <h2 class="dashboard-group-heading dashboard-group-heading--muted">Closed</h2>
                <div class="dashboard-list">
                    @foreach($closedApplications as $application)
                        @include('partials.application-row', ['application' => $application])
                    @endforeach
                </div>
            @endif

            <div class="dashboard-footer">
                <a href="{{ route('careers') }}" class="btn-gst-outline">
                    <i class="bi bi-search"></i> Browse more roles
                </a>
            </div>
        @endif
    </div>
</section><!-- End Dashboard -->

@endsection
