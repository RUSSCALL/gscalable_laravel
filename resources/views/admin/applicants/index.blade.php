@extends('admin.admin_layout')

@section('title', 'Applicants')

@section('main_content')
    @component('admin._page_header', ['eyebrow' => 'Hiring', 'title' => 'Applicants'])
        <a href="{{ route('admin.reports.index', ['export' => 'csv']) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-2"></i> Export CSV
        </a>
    @endcomponent

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.applicants.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="q" class="form-label">Search</label>
                    <input type="search" class="form-control" id="q" name="q" value="{{ request('q') }}"
                           placeholder="Name, email or reference">
                </div>
                <div class="col-md-3">
                    <label for="job" class="form-label">Job</label>
                    <select class="form-select" id="job" name="job">
                        <option value="">All jobs</option>
                        @foreach($jobs as $job)
                            <option value="{{ $job->id }}" @selected(request('job') == $job->id)>{{ $job->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All statuses</option>
                        @foreach(\App\Models\JobApplication::STATUSES as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="rank" class="form-label">Rank</label>
                    <select class="form-select" id="rank" name="rank">
                        <option value="">All ranks</option>
                        @foreach(\App\Models\JobApplication::RANKS as $value => $label)
                            <option value="{{ $value }}" @selected(request('rank') === $value)>{{ $label }}</option>
                        @endforeach
                        <option value="unranked" @selected(request('rank') === 'unranked')>Not ranked</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="sort" class="form-label">Sort</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="">Newest first</option>
                        <option value="rank" @selected(request('sort') === 'rank')>Best fit first</option>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-gst"><i class="bi bi-funnel me-1"></i> Apply filters</button>
                    <a href="{{ route('admin.applicants.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Job</th>
                            <th>Applied</th>
                            <th>Rank</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.applicants.show', $application) }}" class="cell-title">{{ $application->full_name }}</a>@unless($application->reviewed_at)<span class="badge-new">New</span>@endunless
                                    <span class="cell-sub">{{ $application->email }}</span>
                                </td>
                                <td>{{ $application->jobPosting->title ?? 'Deleted job' }}</td>
                                <td class="text-nowrap">{{ $application->created_at->format('M j, Y') }}</td>
                                <td>@include('admin.applicants._rank')</td>
                                <td>@include('admin.applicants._status')</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.applicants.show', $application) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No applications match these filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($applications->hasPages())
            <div class="card-footer">{{ $applications->links() }}</div>
        @endif
    </div>
@endsection
