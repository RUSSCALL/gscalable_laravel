@extends('main_layout')

@section('title', 'Careers — Global Scalable Technologies')
@section('meta_description', 'Explore open roles at Global Scalable Technologies. Cybersecurity, cloud and IT modernization careers supporting U.S. federal agencies and enterprise clients.')

@push('head')
<link rel="canonical" href="{{ url()->current() }}">
@endpush

@section('main_content')

<!-- ======= Careers Hero ======= -->
<section class="careers-hero">
    <div class="container" data-aos="fade-up">
        <div class="section-title">
            <h2>Careers</h2>
            <p>Open Positions</p>
        </div>
        <p class="careers-hero-subtitle">
            We build and defend the systems federal agencies depend on. If you want work that
            matters &mdash; and a team that backs you while you do it &mdash; start here.
        </p>
    </div>
</section>

<!-- ======= Flash messages ======= -->
@if(session('alert_success') || session('alert_status') || session('error'))
    <div class="container careers-flash-wrap">
        @if(session('alert_success'))
            <div class="careers-flash careers-flash--success" role="status">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <span>{{ session('alert_success') }}</span>
            </div>
        @endif
        @if(session('alert_status'))
            <div class="careers-flash" role="status">
                <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                <span>{{ session('alert_status') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="careers-flash careers-flash--error" role="alert">
                <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>
@endif

<!-- ======= Job Board ======= -->
<section id="careers-board" class="careers-board" aria-labelledby="careers-board-heading">
    <div class="container" data-aos="fade-up">
        <h2 id="careers-board-heading" class="visually-hidden">Search open positions</h2>

        <!-- Filter bar -->
        <form method="GET" action="{{ route('careers') }}" class="careers-filter-bar gst-card-base">
            <div class="careers-filter-grid">
                <div class="careers-filter-search">
                    <label for="q">Search roles</label>
                    <input type="search" id="q" name="q" value="{{ request('q') }}"
                           placeholder="Job title, skill or keyword">
                </div>

                <div>
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="location">Location</label>
                    <select id="location" name="location">
                        <option value="">All locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" @selected(request('location') == $location->id)>
                                @if($location->is_remote)
                                    Remote
                                @else
                                    {{ $location->city }}, {{ $location->country }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="employment_type">Employment type</label>
                    <select id="employment_type" name="employment_type">
                        <option value="">Any type</option>
                        @foreach($employmentTypes as $type)
                            <option value="{{ $type }}" @selected(request('employment_type') === $type)>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="experience_level">Experience level</label>
                    <select id="experience_level" name="experience_level">
                        <option value="">Any level</option>
                        @foreach($experienceLevels as $level)
                            <option value="{{ $level }}" @selected(request('experience_level') === $level)>
                                {{ $level }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="salary_min">Minimum salary</label>
                    <select id="salary_min" name="salary_min">
                        <option value="">Any salary</option>
                        @foreach([60000, 80000, 100000, 120000, 150000] as $floor)
                            <option value="{{ $floor }}" @selected(request('salary_min') == $floor)>
                                ${{ number_format($floor) }}+
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="sort">Sort by</label>
                    <select id="sort" name="sort">
                        <option value="newest" @selected($sort === 'newest')>Newest first</option>
                        <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
                        <option value="featured" @selected($sort === 'featured')>Featured first</option>
                        <option value="salary" @selected($sort === 'salary')>Highest salary</option>
                        <option value="deadline" @selected($sort === 'deadline')>Closing soonest</option>
                    </select>
                </div>
            </div>

            <div class="careers-filter-actions">
                <button type="submit" class="btn-gst-primary">
                    <i class="bi bi-search"></i> Search
                </button>
                @if($hasFilters)
                    <a href="{{ route('careers') }}" class="careers-filter-clear">Clear filters</a>
                @endif
            </div>
        </form>

        <!-- Featured roles — only meaningful on an unfiltered board -->
        @if(! $hasFilters && $featuredJobs->isNotEmpty())
            <div class="careers-featured">
                <p class="careers-featured-label">Featured roles</p>
                <div class="careers-featured-strip">
                    @foreach($featuredJobs as $job)
                        @include('partials.job-card', ['job' => $job, 'featured' => true])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Results count. Hidden when nothing is posted at all, since the
             empty-state card below already says so. --}}
        @if($jobPostings->total() > 0 || $hasFilters)
            <div class="careers-results-head">
                <p class="careers-results-count">
                    @if($jobPostings->total() > 0)
                        Showing {{ $jobPostings->firstItem() }}&ndash;{{ $jobPostings->lastItem() }}
                        of {{ $jobPostings->total() }} {{ Str::plural('role', $jobPostings->total()) }}
                    @else
                        No roles found
                    @endif
                </p>
            </div>
        @endif

        @if(count($filters))
            <ul class="careers-active-filters">
                @foreach($filters as $filter)
                    <li>
                        <a href="{{ $filter['remove_url'] }}" class="filter-chip">
                            {{ $filter['label'] }}
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                            <span class="visually-hidden">Remove this filter</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if($jobPostings->count())
            <div class="careers-grid">
                @foreach($jobPostings as $job)
                    @include('partials.job-card', ['job' => $job, 'featured' => false])
                @endforeach
            </div>

            @if($jobPostings->hasPages())
                <div class="careers-pagination">
                    {{ $jobPostings->onEachSide(1)->links() }}
                </div>
            @endif
        @else
            @if($hasFilters)
                <div class="careers-empty gst-card-base">
                    <h3>No roles match those filters</h3>
                    <p>Try widening your search &mdash; or browse everything we have open right now.</p>
                    <a href="{{ route('careers') }}" class="btn-gst-primary">
                        <i class="bi bi-arrow-counterclockwise"></i> View all roles
                    </a>
                </div>
            @else
                {{-- Nothing is posted at all. Offering "view all roles" here would
                     loop back to this same page, so point at job alerts instead. --}}
                <div class="careers-empty gst-card-base">
                    <h3>No openings right now</h3>
                    <p>
                        We're not advertising any roles at the moment, but that changes often.
                        Set up a job alert and we'll email you as soon as something opens.
                    </p>
                    <a href="#job-alerts" class="btn-gst-primary">
                        <i class="bi bi-bell"></i> Get job alerts
                    </a>
                </div>
            @endif
        @endif
    </div>
</section><!-- End Job Board -->

<!-- ======= Job Alerts ======= -->
<section id="job-alerts" class="careers-alert-band">
    <div class="container" data-aos="fade-up">
        <div class="careers-alert-card gst-card-base">
            <div class="careers-alert-copy">
                <h2>Nothing quite right yet?</h2>
                <p>
                    Tell us what you're looking for and we'll email you when a matching
                    role opens. Confirm once, unsubscribe any time.
                </p>
            </div>

            <form method="POST" action="{{ route('careers.alerts.subscribe') }}"
                  class="careers-alert-form">
                @csrf

                {{-- Bot defences, mirroring the application form: an off-screen
                     honeypot and an encrypted render timestamp. --}}
                <div class="hp-field" aria-hidden="true">
                    <label for="alert_website">Leave this field blank</label>
                    <input type="text" id="alert_website" name="website" tabindex="-1" autocomplete="off">
                </div>
                <input type="hidden" name="_ts" value="{{ Crypt::encryptString(time()) }}">

                <div class="careers-alert-fields">
                    <div class="careers-alert-field">
                        <label for="alert_email">Email address</label>
                        <input type="email" id="alert_email" name="email" required
                               value="{{ old('email') }}" placeholder="you@example.com"
                               autocomplete="email">
                        @error('email')
                            <p class="careers-alert-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="careers-alert-field">
                        <label for="alert_category">Category <span>(optional)</span></label>
                        <select id="alert_category" name="category_id">
                            <option value="">Any category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="careers-alert-field">
                        <label for="alert_keywords">Keywords <span>(optional)</span></label>
                        <input type="text" id="alert_keywords" name="keywords"
                               value="{{ old('keywords') }}" maxlength="120"
                               placeholder="e.g. cloud security">
                        @error('keywords')
                            <p class="careers-alert-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="careers-alert-submit">
                        <button type="submit" class="btn-gst-primary">
                            <i class="bi bi-bell"></i> Notify me
                        </button>
                    </div>
                </div>

                <p class="careers-alert-fineprint">
                    We only use this address for job alerts. See our
                    <a href="{{ route('privacy') }}">privacy policy</a>.
                </p>
            </form>
        </div>
    </div>
</section><!-- End Job Alerts -->

@endsection
