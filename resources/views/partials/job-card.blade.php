@php
    /**
     * One job on the board. Used by both the featured strip and the main grid.
     *
     * @var \App\Models\JobPosting $job
     * @var bool $featured  Show the Featured pill (the strip already implies it).
     */
    $deadline = \Carbon\Carbon::parse($job->application_deadline)->endOfDay();
    // Signed difference: negative once the deadline has passed.
    $daysLeft = (int) now()->startOfDay()->diffInDays($deadline, false);
@endphp

<a href="{{ route('careers.show', $job->slug) }}" class="job-card gst-card-base">
    <span class="job-card-eyebrow">
        <span>
            @if($job->location)
                {{ $job->location->is_remote ? 'Remote' : $job->location->city . ', ' . $job->location->country }}
            @else
                Location flexible
            @endif
        </span>
        <span>{{ date('Y') }}-{{ $job->id }}</span>
    </span>

    <h3 class="job-card-title">{{ $job->title }}</h3>

    <p class="job-card-desc">{{ Str::limit(strip_tags($job->description), 160) }}</p>

    <span class="job-card-meta">
        @if(($featured ?? false) || $job->is_featured)
            <span class="job-pill job-pill--featured">Featured</span>
        @endif
        <span class="job-pill">{{ $job->employment_type }}</span>
        <span class="job-pill">{{ $job->experience_level }}</span>

        @if($daysLeft < 0)
            <span class="badge-deadline badge-deadline--closed">Closed</span>
        @elseif($daysLeft <= 5)
            <span class="badge-deadline badge-deadline--warn">
                {{ $daysLeft === 0 ? 'Closes today' : $daysLeft . ' ' . Str::plural('day', $daysLeft) . ' left' }}
            </span>
        @else
            <span class="badge-deadline">Apply by {{ $deadline->format('M j, Y') }}</span>
        @endif
    </span>
</a>
