@extends('admin.admin_layout')

@section('title', 'Job Alerts')

@section('main_content')

    <!-- Main Content -->
    @component('admin._page_header', ['eyebrow' => 'Hiring', 'title' => 'Job Alert Subscribers'])
        <a href="{{ route('admin.job-alerts', array_merge(request()->query(), ['export' => 'csv'])) }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-download me-2"></i> Export CSV
        </a>
    @endcomponent

    <div class="row g-3 mb-4">
        @foreach([
            ['bi-envelope-check', 'Confirmed', $confirmedCount],
            ['bi-hourglass-split', 'Awaiting confirmation', $pendingCount],
        ] as [$icon, $label, $value])
            <div class="col-md-4 col-6">
                <div class="card stats-card h-100 mb-0">
                    <div class="card-body">
                        <span class="icon-box"><i class="bi {{ $icon }}"></i></span>
                        <div>
                            <p class="stat-label">{{ $label }}</p>
                            <p class="stat-value">{{ number_format($value) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.job-alerts') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="q" class="form-label">Search email</label>
                    <input type="search" class="form-control" id="q" name="q"
                           value="{{ $search }}" placeholder="name@example.com">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All</option>
                        <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-gst me-2">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.job-alerts') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Wants to hear about</th>
                            <th>Status</th>
                            <th>Subscribed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->email }}</td>
                                <td>{{ $subscription->target_description }}</td>
                                <td>
                                    @if($subscription->is_confirmed)
                                        <span class="status-badge status-badge--positive">Confirmed</span>
                                    @else
                                        <span class="status-badge status-badge--active">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $subscription->created_at->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No subscribers yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($subscriptions->hasPages())
            <div class="card-footer">{{ $subscriptions->links() }}</div>
        @endif
    </div>

@endsection
