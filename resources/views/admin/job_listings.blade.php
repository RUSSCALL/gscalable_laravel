@extends('admin.admin_layout')

@section('title', 'Job Listings')

@section('main_content')
    @component('admin._page_header', ['eyebrow' => 'Hiring', 'title' => 'Job Listings'])
        <a href="{{ route('jobs.create') }}" class="btn btn-gst">
            <i class="bi bi-plus-lg me-2"></i> Create Job Listing
        </a>
    @endcomponent

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                @if($filter)
                    <input type="hidden" name="filter" value="{{ $filter }}">
                @endif
                <div class="col-md-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="location" class="form-label">Location</label>
                    <select class="form-select" id="location" name="location">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ request('location') == $location->id ? 'selected' : '' }}>
                                {{ $location->city }}, {{ $location->country }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" placeholder="Search jobs..." value="{{ request('search') }}">
                        <button class="btn btn-gst" type="submit" aria-label="Search">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Job Listings Table -->
    <div class="card">
        <div class="card-header">
            <h5>All job listings</h5>
            @php
                $listingQuery = request()->except(['page', 'export']);
            @endphp
            <div class="d-flex align-items-center gap-2">
                @if($filter)
                    <a href="{{ route('jobListings', \Illuminate\Support\Arr::except($listingQuery, 'filter')) }}"
                       class="btn btn-sm btn-light border" title="Remove this filter">
                        {{ \App\Http\Controllers\AdminController::LISTING_FILTERS[$filter] }} <i class="bi bi-x"></i>
                    </a>
                @endif
                <a href="{{ route('jobListings', $listingQuery + ['export' => 'csv']) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-download me-1"></i> Export
                </a>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach(\App\Http\Controllers\AdminController::LISTING_FILTERS as $value => $label)
                            <li>
                                <a @class(['dropdown-item', 'active' => $filter === $value])
                                   href="{{ route('jobListings', ['filter' => $value] + $listingQuery) }}">{{ $label }}</a>
                            </li>
                        @endforeach
                        @if($filter)
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('jobListings', \Illuminate\Support\Arr::except($listingQuery, 'filter')) }}">Show all</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Applications</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jobPostings as $job)
                        <tr>
                            <td>
                                <a href="{{ route('jobs.show', $job->id) }}" class="cell-title">{{ $job->title }}</a>
                                <span class="cell-sub">{{ $job->employment_type }} • {{ $job->experience_level }}</span>
                            </td>
                            <td>{{ $job->category->name ?? 'Uncategorized' }}</td>
                            <td>
                                @if($job->work_arrangement === 'remote' || $job->location?->is_remote)
                                    Remote
                                @elseif($job->location)
                                    {{ $job->location->city }}, {{ $job->location->country }}
                                    @if($job->work_arrangement === 'hybrid')<span class="cell-sub">Hybrid</span>@endif
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.applicants.index', ['job' => $job->id]) }}">{{ $job->applications_count }}</a>
                            </td>
                            <td>
                                @php
                                    $deadline = $job->application_deadline;
                                    $daysLeft = (int) today()->diffInDays($deadline, false);
                                @endphp

                                @if($daysLeft < 0)
                                    <span class="status-badge status-badge--closed">Expired</span>
                                @elseif($daysLeft <= 5)
                                    <span class="status-badge status-badge--active">{{ $daysLeft === 0 ? 'Closes today' : $daysLeft . ' ' . \Illuminate\Support\Str::plural('day', $daysLeft) . ' left' }}</span>
                                @else
                                    <span class="text-nowrap">{{ $deadline->format('M d, Y') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($job->is_published)
                                    <span class="status-badge status-badge--positive">Published</span>
                                @else
                                    <span class="status-badge status-badge--neutral">Draft</span>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $job->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'
                                            aria-expanded="false" aria-label="Actions for {{ $job->title }}">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('jobs.edit', $job->id) }}">
                                            <i class="bi bi-pencil me-2"></i> Edit
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('jobs.show', $job->id) }}">
                                            <i class="bi bi-info-circle me-2"></i> Details
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.applicants.index', ['job' => $job->id]) }}">
                                            <i class="bi bi-people me-2"></i> View applications
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        @if($job->is_published)
                                            <li>
                                                <form action="{{ route('jobs.unpublish', $job->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-file-earmark-lock me-2"></i> Unpublish
                                                    </button>
                                                </form>
                                            </li>
                                        @else
                                            <li>
                                                <form action="{{ route('jobs.publish', $job->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-file-earmark-check me-2"></i> Publish
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        <li>
                                            <form action="{{ route('jobs.destroy', $job->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this job listing?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <span class="empty-state-icon"><i class="bi bi-folder-x"></i></span>
                                    <h3>No job listings found</h3>
                                    <p>Try other filters, or create a listing to start taking applications.</p>
                                    <a href="{{ route('jobs.create') }}" class="btn btn-gst">Create a job listing</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($jobPostings->hasPages())
            <div class="card-footer">
                {{ $jobPostings->links() }}
            </div>
        @endif
    </div>
@endsection
