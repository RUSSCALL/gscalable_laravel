@extends('admin.admin_layout')

@section('main_content')
    @php
        $expired = $job->application_deadline->isBefore(today());
    @endphp
    <div class="main-content">
        <div class="container-fluid p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h1 class="page-title mb-1">{{ $job->title }}</h1>
                    @if($job->is_published && ! $expired)
                        <span class="status-badge status-active">Live on careers page</span>
                    @elseif($job->is_published)
                        <span class="status-badge status-closed">Published, deadline passed</span>
                    @else
                        <span class="status-badge status-pending">Draft</span>
                    @endif
                    @if($job->is_featured)<span class="badge bg-warning text-dark ms-1">Featured</span>@endif
                </div>
                <div class="d-flex gap-2">
                    @if($job->is_published && ! $expired)
                        <a href="{{ route('careers.show', $job->slug) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                            <i class="bi bi-box-arrow-up-right me-2"></i> View public page
                        </a>
                    @endif
                    <a href="{{ route('jobs.edit', $job->id) }}" class="btn btn-gst">
                        <i class="bi bi-pencil me-2"></i> Edit
                    </a>
                    <a href="{{ route('jobListings') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i> Back
                    </a>
                </div>
            </div>

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
                            <dl class="mb-0">
                                <dt>Category</dt><dd>{{ $job->category->name ?? 'Uncategorized' }}</dd>
                                <dt>Location</dt>
                                <dd>
                                    @if($job->location?->is_remote) Remote
                                    @elseif($job->location) {{ $job->location->city }}, {{ $job->location->country }}
                                    @else &mdash; @endif
                                </dd>
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
                </div>
            </div>
        </div>
    </div>
@endsection
