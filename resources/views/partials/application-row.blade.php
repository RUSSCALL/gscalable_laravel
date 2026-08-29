@php
    /**
     * One application on the applicant dashboard.
     *
     * @var \App\Models\JobApplication $application
     */
    $job = $application->jobPosting;
@endphp

<article class="application-row gst-card-base">
    <div class="application-row-main">
        <p class="application-row-reference">{{ $application->reference }}</p>

        <h3 class="application-row-title">
            @if($job)
                {{-- Links to the live posting; withdrawn roles keep the title as plain text. --}}
                <a href="{{ route('careers.show', $job->slug) }}">{{ $job->title }}</a>
            @else
                This role is no longer listed
            @endif
        </h3>

        <p class="application-row-meta">
            Applied {{ $application->created_at->format('M j, Y') }}
            @if($job)
                <span class="application-row-sep">&middot;</span> {{ $job->employment_type }}
            @endif
        </p>
    </div>

    <div class="application-row-status">
        <span class="status-badge status-badge--{{ $application->status_tone }}">
            {{ $application->status_label }}
        </span>
        @if($application->status_description)
            <p class="application-row-note">{{ $application->status_description }}</p>
        @endif
    </div>
</article>
