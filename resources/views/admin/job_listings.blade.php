@extends('admin.admin_layout')

@section('main_content')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title">Job Listings</h1>
                <button type="button" class="btn btn-gst" data-bs-toggle="modal" data-bs-target="#createJobModal">
                    <i class="bi bi-plus-circle me-2"></i> Create Job Listing
                </button>
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
                                            $deadline = \Carbon\Carbon::parse($job->application_deadline);
                                            $daysLeft = $deadline->diffInDays(now());
                                        @endphp
                                        
                                        @if($deadline->isPast())
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
                                                <li>
                                                    {{-- <a class="dropdown-item" href="{{ route('jobs.applications', $job->id) }}"> --}}
                                                    <i class="bi bi-people me-2"></i> View Applications
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('jobs.show', $job->id) }}">
                                                    <i class="bi bi-eye me-2"></i> Preview
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
                                            <button type="button" class="btn btn-sm btn-gst" data-bs-toggle="modal" data-bs-target="#createJobModal">
                                                Create your first job listing
                                            </button>
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

    <!-- Create Job Modal -->
    <div class="modal fade" id="createJobModal" tabindex="-1" aria-labelledby="createJobModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createJobModalLabel">Create New Job Listing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createJobForm" action="{{ route('jobs.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="location_id" class="form-label">Location</label>
                                <select class="form-select" id="location_id" name="location_id">
                                    <option value="">Select Location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">
                                            {{ $location->city }}, {{ $location->country }}
                                            @if($location->is_remote) (Remote) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_remote" name="is_remote">
                                    <label class="form-check-label" for="is_remote">
                                        This is a remote position
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="employment_type" class="form-label">Employment Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="employment_type" name="employment_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Temporary">Temporary</option>
                                    <option value="Internship">Internship</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="experience_level" class="form-label">Experience Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="experience_level" name="experience_level" required>
                                    <option value="">Select Level</option>
                                    <option value="Entry">Entry Level</option>
                                    <option value="Mid">Mid Level</option>
                                    <option value="Senior">Senior Level</option>
                                    <option value="Executive">Executive Level</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="salary_min" class="form-label">Minimum Salary</label>
                                <input type="number" class="form-control" id="salary_min" name="salary_min" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label for="salary_max" class="form-label">Maximum Salary</label>
                                <input type="number" class="form-control" id="salary_max" name="salary_max" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label for="salary_period" class="form-label">Salary Period</label>
                                <select class="form-select" id="salary_period" name="salary_period">
                                    <option value="">Select Period</option>
                                    <option value="Hourly">Hourly</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Annual">Annual</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="application_deadline" class="form-label">Application Deadline <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="application_deadline" name="application_deadline" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Job Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="requirements" class="form-label">Requirements <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="requirements" name="requirements" rows="4" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="responsibilities" class="form-label">Responsibilities <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="responsibilities" name="responsibilities" rows="4" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="benefits" class="form-label">Benefits</label>
                            <textarea class="form-control" id="benefits" name="benefits" rows="4"></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                                    <label class="form-check-label" for="is_featured">Feature this job</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published">
                                    <label class="form-check-label" for="is_published">Publish immediately</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-gst" onclick="document.getElementById('createJobForm').submit()">Create Job Listing</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Initialize any form plugins (Rich text editors, datepickers, etc.)
    document.addEventListener('DOMContentLoaded', function() {
        // You can add custom JavaScript for the page here
        // For example, initialize WYSIWYG editors for description fields
    });
</script>
@endsection