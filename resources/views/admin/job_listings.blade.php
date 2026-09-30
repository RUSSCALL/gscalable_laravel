@extends('admin.admin_layout')

@section('main_content')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title">Job Listings</h1>
                <a href="{{ route('jobs.create') }}" class="btn btn-gst">
                    <i class="bi bi-plus-circle me-2"></i> Create Job Listing
                </a>
            </div>
            
            <!-- Filters and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="" method="GET" class="row g-3">
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
                                <button class="btn btn-gst" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Job Listings Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Job Listings</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-download me-1"></i> Export
                        </button>
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Featured Jobs</a></li>
                                <li><a class="dropdown-item" href="#">Most Applications</a></li>
                                <li><a class="dropdown-item" href="#">Recently Added</a></li>
                                <li><a class="dropdown-item" href="#">Expiring Soon</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Category</th>
                                    <th>Location</th>
                                    <th>Applications</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jobPostings as $job)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ $job->title }}</h6>
                                                <small class="text-muted">{{ $job->employment_type }} • {{ $job->experience_level }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $job->category->name ?? 'Uncategorized' }}</td>
                                    <td>
                                        @if($job->location)
                                            @if($job->location->is_remote)
                                                <span class="badge bg-info">Remote</span>
                                            @else
                                                {{ $job->location->city }}, {{ $job->location->country }}
                                            @endif
                                        @else
                                            --
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $job->applications_count }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $deadline = $job->application_deadline;
                                            $daysLeft = (int) today()->diffInDays($deadline, false);
                                        @endphp

                                        @if($daysLeft < 0)
                                            <span class="badge bg-danger">Expired</span>
                                        @elseif($daysLeft <= 5)
                                            <span class="badge bg-warning">{{ $daysLeft }} days left</span>
                                        @else
                                            {{ $deadline->format('M d, Y') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($job->is_published)
                                            <span class="status-badge status-active">Published</span>
                                        @else
                                            <span class="status-badge status-pending">Draft</span>
                                        @endif
                                    </td>
                                    <td>{{ $job->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('jobs.edit', $job->id) }}">
                                                    <i class="bi bi-pencil me-2"></i> Edit
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('jobs.show', $job->id) }}">
                                                    <i class="bi bi-info-circle me-2"></i> Details
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                @if($job->is_published)
                                                    <li>
                                                        <form action="{{ route('jobs.unpublish', $job->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="dropdown-item text-warning">
                                                                <i class="bi bi-file-earmark-lock me-2"></i> Unpublish
                                                            </button>
                                                        </form>
                                                    </li>
                                                @else
                                                    <li>
                                                        <form action="{{ route('jobs.publish', $job->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="dropdown-item text-success">
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
                                    <td colspan="8" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-folder-x" style="font-size: 2rem;"></i>
                                            <p class="mt-2">No job listings found</p>
                                            <a href="{{ route('jobs.create') }}" class="btn btn-sm btn-gst">
                                                Create your first job listing
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        {{ $jobPostings->links() }}
                    </div>
                </div>

            </div>
            
            <!-- Footer -->
            <div class="footer mt-auto">
                <p class="mb-0">©{{ date('Y') }} Global Scalable Technologies (GST). All rights reserved.</p>
            </div>
        </div>
    </div>
@endsection
