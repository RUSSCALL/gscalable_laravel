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

        <!-- Results -->
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
            <div class="careers-empty gst-card-base">
                <h3>No roles match those filters</h3>
                <p>Try widening your search &mdash; or browse everything we have open right now.</p>
                <a href="{{ route('careers') }}" class="btn-gst-primary">
                    <i class="bi bi-arrow-counterclockwise"></i> View all roles
                </a>
            </div>
        @endif
    </div>
</section><!-- End Job Board -->

@endsection
