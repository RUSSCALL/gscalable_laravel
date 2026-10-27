@extends('admin.admin_layout')

@section('title', $job->title)

@section('crumb_parent')
    <a href="{{ route('jobListings') }}">Job Listings</a>
@endsection

@section('main_content')
    @php
        $expired = $job->application_deadline->isBefore(today());
    @endphp
    @component('admin._page_header', ['eyebrow' => 'Job listing', 'title' => $job->title])
        @slot('meta')
            @if($job->is_published && ! $expired)
                <span class="status-badge status-badge--positive">Live on careers page</span>
            @elseif($job->is_published)
                <span class="status-badge status-badge--closed">Published, deadline passed</span>
            @else
                <span class="status-badge status-badge--neutral">Draft</span>
            @endif
            @if($job->is_featured)<span class="status-badge status-badge--active"><i class="bi bi-star-fill"></i> Featured</span>@endif
        @endslot
        <a href="{{ route('jobListings') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back
        </a>
        @if($job->is_published && ! $expired)
            <a href="{{ route('careers.show', $job->slug) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                <i class="bi bi-box-arrow-up-right me-2"></i> View public page
            </a>
        @endif
        <a href="{{ route('admin.applicants.index', ['job' => $job->id]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-people me-2"></i> View applications
        </a>
        <a href="{{ route('jobs.edit', $job->id) }}" class="btn btn-gst">
            <i class="bi bi-pencil me-2"></i> Edit
        </a>
    @endcomponent

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @foreach(['description' => 'Description', 'responsibilities' => 'Responsibilities', 'requirements' => 'Requirements', 'benefits' => 'Benefits'] as $field => $label)
                        @if($job->$field)
                            <h5>{{ $label }}</h5>
                            <div class="mb-3">{!! \App\Support\RichText::render($job->$field) !!}</div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <dl class="detail-list mb-0">
                        <dt>Category</dt><dd>{{ $job->category->name ?? 'Uncategorized' }}</dd>
                        <dt>Location</dt>
                        <dd>
                            @if($job->location?->is_remote) Remote
                            @elseif($job->location) {{ $job->location->city }}, {{ $job->location->country }}
                            @else &mdash; @endif
                        </dd>
                        <dt>Work arrangement</dt><dd>{{ $job->work_arrangement_label }}</dd>
                        <dt>Employment type</dt><dd>{{ $job->employment_type }}</dd>
                        <dt>Experience level</dt><dd>{{ $job->experience_level }}</dd>
                        <dt>Salary</dt><dd>{{ $job->salary_range }}</dd>
                        <dt>Application deadline</dt><dd>{{ $job->application_deadline->format('M d, Y') }}</dd>
                        <dt>Applications</dt><dd>{{ $job->applications_count }}</dd>
                        <dt>Public URL</dt><dd class="text-break"><code>{{ route('careers.show', $job->slug) }}</code></dd>
                        <dt>Published</dt><dd>{{ $job->published_at?->format('M d, Y') ?? '—' }}</dd>
                        <dt>Created</dt><dd>{{ $job->created_at->format('M d, Y') }}@if($job->creator) by {{ $job->creator->name }}@endif</dd>
                    </dl>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Screener questions</h5></div>
                <div class="card-body">
                    @forelse($job->screenerQuestions as $question)
                        <div class="mb-2">
                            <div>{{ $question->question }}</div>
                            <small class="text-muted">
                                {{ \App\Models\JobScreenerQuestion::IMPORTANCE[$question->importance] ?? $question->importance }}
                                &middot; needs "{{ $question->required_answer ? 'Yes' : 'No' }}"
                            </small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">None. Applications for this job aren't ranked.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
