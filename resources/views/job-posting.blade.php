@extends('main_layout')

@php
    $deadline = \Carbon\Carbon::parse($job->application_deadline)->endOfDay();
    $daysLeft = (int) now()->startOfDay()->diffInDays($deadline, false);
    $isOpen = $daysLeft >= 0;
    $metaDescription = Str::limit(strip_tags($job->description), 155);
    $canonical = route('careers.show', $job->slug);

    $locationLabel = $job->location
        ? ($job->location->is_remote ? 'Remote' : $job->location->city . ', ' . $job->location->country)
        : 'Location flexible';
@endphp

@section('title', $job->title . ' — Careers — Global Scalable Technologies')
@section('meta_description', $metaDescription)

@push('head')
<link rel="canonical" href="{{ $canonical }}">
<meta property="og:type" content="article">
<meta property="og:title" content="{{ $job->title }} — Global Scalable Technologies">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $canonical }}">
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('main_content')

<!-- ======= Job Detail Hero ======= -->
<section class="job-detail-hero">
    <div class="container" data-aos="fade-up">
        <div class="careers-back-row">
            <a href="{{ route('careers') }}" class="careers-back-link">
                <i class="bi bi-arrow-left"></i> All open roles
            </a>
        </div>

        <h1 class="job-detail-title">{{ $job->title }}</h1>

        <div class="job-detail-meta">
            @if($job->is_featured)
                <span class="job-pill job-pill--featured">Featured</span>
            @endif
            <span class="job-pill"><i class="bi bi-geo-alt"></i> {{ $locationLabel }}</span>
            <span class="job-pill"><i class="bi bi-briefcase"></i> {{ $job->employment_type }}</span>
            <span class="job-pill"><i class="bi bi-bar-chart"></i> {{ $job->experience_level }}</span>
            @if($job->category)
                <span class="job-pill"><i class="bi bi-bookmark"></i> {{ $job->category->name }}</span>
            @endif

            @if(! $isOpen)
                <span class="badge-deadline badge-deadline--closed">Closed</span>
            @elseif($daysLeft <= 5)
                <span class="badge-deadline badge-deadline--warn">
                    {{ $daysLeft === 0 ? 'Closes today' : $daysLeft . ' ' . Str::plural('day', $daysLeft) . ' left' }}
                </span>
            @else
                <span class="badge-deadline">Apply by {{ $deadline->format('M j, Y') }}</span>
            @endif
        </div>
    </div>
</section>

<!-- ======= Job Detail Body ======= -->
<section class="job-detail-body-band">
    <div class="container" data-aos="fade-up">
        <div class="row">
            <div class="col-lg-8">
                <div class="job-detail-body">
                    @if($job->salary_min || $job->salary_max)
                        <div class="job-section">
                            <h2>Compensation</h2>
                            <p>{{ $job->salary_range }}</p>
                        </div>
                    @endif

                    <div class="job-section">
                        <h2>About the role</h2>
                        {{-- Stored copy is plain text with newlines, so escape it
                             and convert the breaks rather than echoing raw HTML. --}}
                        <p>{!! nl2br(e($job->description)) !!}</p>
                    </div>

                    @if($job->responsibilities)
                        <div class="job-section">
                            <h2>Responsibilities</h2>
                            <p>{!! nl2br(e($job->responsibilities)) !!}</p>
                        </div>
                    @endif

                    @if($job->requirements)
                        <div class="job-section">
                            <h2>Requirements</h2>
                            <p>{!! nl2br(e($job->requirements)) !!}</p>
                        </div>
                    @endif

                    @if($job->benefits)
                        <div class="job-section">
                            <h2>Benefits</h2>
                            <p>{!! nl2br(e($job->benefits)) !!}</p>
                        </div>
                    @endif

                    <div class="job-detail-stats">
                        <span><i class="bi bi-eye"></i> {{ number_format($job->views_count) }} views</span>
                        <span><i class="bi bi-people"></i> {{ number_format($job->applications_count) }} applications</span>
                        @if($job->published_at)
                            <span><i class="bi bi-calendar-check"></i>
                                Posted {{ \Carbon\Carbon::parse($job->published_at)->format('M j, Y') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Aside -->
            <div class="col-lg-4">
                <div class="job-detail-aside">
                    <div class="job-apply-card gst-card-base">
                        @if(! $isOpen)
                            <h2>Applications closed</h2>
                            <p class="job-apply-closed">
                                This role is no longer accepting applications.
                            </p>
                            <a href="{{ route('careers') }}" class="btn-gst-outline">
                                <i class="bi bi-arrow-left"></i> Browse open roles
                            </a>
                        @elseif($hasApplied)
                            <h2>Application received</h2>
                            <p>We have your application for this role and will be in touch.</p>
                            <a href="{{ route('careers') }}" class="btn-gst-outline">
                                <i class="bi bi-arrow-left"></i> Browse other roles
                            </a>
                        @else
                            <h2>Ready to apply?</h2>
                            <p>It takes a few minutes. Have your resume ready.</p>
                            <div class="job-apply-actions">
                                @auth
                                    <a href="{{ route('careers.apply', $job->slug) }}" class="btn-gst-primary">
                                        <i class="bi bi-send"></i> Apply now
                                    </a>
                                @else
                                    {{-- Send the visitor back to this job's form after login,
                                         instead of dropping them on a dashboard. --}}
                                    <a href="{{ route('login', ['redirect' => route('careers.apply', $job->slug)]) }}"
                                       class="btn-gst-primary">
                                        <i class="bi bi-send"></i> Apply now
                                    </a>
                                @endauth

                                <button type="button" class="btn-gst-outline"
                                        onclick="shareJob(@js($job->title), @js($canonical))">
                                    <i class="bi bi-share"></i> Share role
                                </button>
                            </div>
                        @endif

                        <ul class="job-facts">
                            <li>
                                <span class="job-fact-label">Type</span>
                                <span class="job-fact-value">{{ $job->employment_type }}</span>
                            </li>
                            <li>
                                <span class="job-fact-label">Level</span>
                                <span class="job-fact-value">{{ $job->experience_level }}</span>
                            </li>
                            <li>
                                <span class="job-fact-label">Location</span>
                                <span class="job-fact-value">{{ $locationLabel }}</span>
                            </li>
                            @if($job->salary_min || $job->salary_max)
                                <li>
                                    <span class="job-fact-label">Salary</span>
                                    <span class="job-fact-value">{{ $job->salary_range }}</span>
                                </li>
                            @endif
                            <li>
                                <span class="job-fact-label">Closes</span>
                                <span class="job-fact-value">{{ $deadline->format('M j, Y') }}</span>
                            </li>
                            <li>
                                <span class="job-fact-label">Job ID</span>
                                <span class="job-fact-value">{{ date('Y') }}-{{ $job->id }}</span>
                            </li>
                        </ul>
                    </div>

                    @if($relatedJobs->isNotEmpty())
                        <div class="job-similar-card gst-card-base">
                            <h2>Similar roles</h2>
                            @foreach($relatedJobs as $relatedJob)
                                <a href="{{ route('careers.show', $relatedJob->slug) }}" class="similar-role">
                                    <p class="similar-role-title">{{ $relatedJob->title }}</p>
                                    <p class="similar-role-meta">
                                        @if($relatedJob->location)
                                            {{ $relatedJob->location->is_remote ? 'Remote' : $relatedJob->location->city }}
                                        @else
                                            Location flexible
                                        @endif
                                        &middot; {{ $relatedJob->employment_type }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section><!-- End Job Detail -->

@endsection
