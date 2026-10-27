@extends('admin.admin_layout')

@section('title', 'Dashboard')

@php
    $newCount = $dashboardStats['new_applications'];
    $subtitle = now()->format('l, F j, Y')
        . ($newCount ? ' · ' . $newCount . ' new ' . \Illuminate\Support\Str::plural('application', $newCount) . ' to review' : '');
    $jobTones = ['Active' => 'positive', 'Expired' => 'closed', 'Draft' => 'neutral'];
@endphp

@section('main_content')
    @component('admin._page_header', ['eyebrow' => 'Overview', 'title' => 'Dashboard', 'subtitle' => $subtitle])
        <a href="{{ route('jobs.create') }}" class="btn btn-gst"><i class="bi bi-plus-lg me-2"></i>New job listing</a>
    @endcomponent

    <div class="row g-3 mb-4">
        @foreach([
            ['bi-briefcase', 'Active job listings', $dashboardStats['active_jobs'], 'Open for applications'],
            ['bi-people', 'New applications', $dashboardStats['new_applications'], 'Not reviewed yet'],
            ['bi-calendar-check', 'Interviews scheduled', $dashboardStats['interviews_scheduled'], 'Upcoming'],
            ['bi-graph-up', 'Hired this month', $dashboardStats['hired_this_month'], 'Since ' . now()->startOfMonth()->format('M j')],
        ] as [$icon, $label, $value, $note])
            <div class="col-xl-3 col-6">
                <div class="card stats-card h-100 mb-0">
                    <div class="card-body">
                        <span class="icon-box"><i class="bi {{ $icon }}"></i></span>
                        <div>
                            <p class="stat-label">{{ $label }}</p>
                            <p class="stat-value">{{ number_format($value) }}</p>
                            <p class="stat-note">{{ $note }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5>Recent job listings</h5>
                    <a href="{{ route('jobListings') }}" class="btn btn-sm btn-outline-secondary">View all</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Job title</th>
                                    <th>Location</th>
                                    <th>Applications</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentJobs as $job)
                                    <tr>
                                        <td>
                                            <a href="{{ route('jobs.show', $job['id']) }}" class="cell-title">{{ $job['title'] }}</a>
                                            <span class="cell-sub">{{ $job['department'] }}</span>
                                        </td>
                                        <td>{{ $job['location'] ?: '—' }}</td>
                                        <td>{{ $job['applications'] }}</td>
                                        <td><span class="status-badge status-badge--{{ $jobTones[$job['status']] }}">{{ $job['status'] }}</span></td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'
                                                        aria-expanded="false" aria-label="Actions for {{ $job['title'] }}">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('jobs.edit', $job['id']) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('admin.applicants.index', ['job' => $job['id']]) }}"><i class="bi bi-people me-2"></i>View applications</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No job listings yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h5>Recent activity</h5></div>
                <ul class="list-group list-group-flush">
                    @forelse($recentActivity as $activity)
                        <li class="list-group-item">
                            <a href="{{ $activity['url'] }}" class="d-flex gap-3 align-items-start text-decoration-none text-reset">
                                <span class="activity-icon"><i class="bi {{ $activity['icon'] }}"></i></span>
                                <span>
                                    <span class="d-block">{{ $activity['text'] }} <strong>{{ $activity['subject'] }}</strong></span>
                                    <small class="text-muted">{{ $activity['at']->diffForHumans() }}</small>
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No activity yet.</li>
                    @endforelse
                </ul>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.applicants.index') }}">View all applicants</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5>Quick actions</h5></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('jobs.create') }}" class="btn btn-gst">
                            <i class="bi bi-plus-circle me-2"></i> Add new job listing
                        </a>
                        <a href="{{ route('admin.applicants.index', ['sort' => 'rank']) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-person-lines-fill me-2"></i> Review applications
                        </a>
                        <a href="{{ route('admin.interviews.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-calendar-plus me-2"></i> Schedule interviews
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-file-earmark-text me-2"></i> Generate reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
