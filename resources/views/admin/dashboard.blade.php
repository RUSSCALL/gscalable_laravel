@extends('admin.admin_layout')

@section('main_content')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid p-4">
            <h1 class="page-title">Dashboard</h1>
            
            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-briefcase"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Active Job Listings</h6>
                                <h3 class="mb-0">{{ $dashboardStats['active_jobs'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">New Applications</h6>
                                <h3 class="mb-0">{{ $dashboardStats['new_applications'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Interviews Scheduled</h6>
                                <h3 class="mb-0">{{ $dashboardStats['interviews_scheduled'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Hired This Month</h6>
                                <h3 class="mb-0">{{ $dashboardStats['hired_this_month'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Job Listings Section -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Job Listings</h5>
                            <a href="#" class="btn btn-sm btn-gst">+ Add New Job</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Job Title</th>
                                            <th>Department</th>
                                            <th>Location</th>
                                            <th>Applications</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentJobs as $job)
                                        <tr>
                                            <td>{{ $job['title'] }}</td>
                                            <td>{{ $job['department'] }}</td>
                                            <td>{{ $job['location'] }}</td>
                                            <td>{{ $job['applications'] }}</td>
                                            <td>
                                                @if ($job['status'] === 'Active')
                                                    <span class="status-badge status-active">Active</span>
                                                @elseif ($job['status'] === 'Pending')
                                                    <span class="status-badge status-pending">Pending</span>
                                                @else
                                                    <span class="status-badge status-closed">Closed</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="">Edit</a></li>
                                                        <li><a class="dropdown-item" href="">View Applications</a></li>
                                                        {{-- @if ($job['status'] === 'Closed')
                                                            <li><a class="dropdown-item" href="">Reopen Position</a></li>
                                                        @else
                                                            <li><a class="dropdown-item" href="">Close Position</a></li>
                                                        @endif --}}
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No job listings available</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Recent Activity Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Activity</h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-primary">
                                            <i class="bi bi-person-plus"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0">New application for <strong>Network Engineer</strong></p>
                                            <small class="text-muted">10 minutes ago</small>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-success">
                                            <i class="bi bi-check-circle"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0">Interview scheduled for <strong>Cybersecurity Analyst</strong></p>
                                            <small class="text-muted">2 hours ago</small>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-warning">
                                            <i class="bi bi-pencil-square"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0">Job listing updated for <strong>DevOps Engineer</strong></p>
                                            <small class="text-muted">5 hours ago</small>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-danger">
                                            <i class="bi bi-x-circle"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0">Position closed for <strong>Project Manager</strong></p>
                                            <small class="text-muted">Yesterday</small>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <a href="#" class="text-decoration-none">View All Activities</a>
                        </div>
                    </div>
                    
                    <!-- Quick Actions Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="#" class="btn btn-gst mb-2">
                                    <i class="bi bi-plus-circle me-2"></i> Add New Job Listing
                                </a>
                                <a href="#" class="btn btn-outline-secondary mb-2">
                                    <i class="bi bi-person-lines-fill me-2"></i> Review Applications
                                </a>
                                <a href="#" class="btn btn-outline-secondary mb-2">
                                    <i class="bi bi-calendar-plus me-2"></i> Schedule Interviews
                                </a>
                                <a href="#" class="btn btn-outline-secondary">
                                    <i class="bi bi-file-earmark-text me-2"></i> Generate Reports
                                </a>
                            </div>
                        </div>
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