@extends('admin.admin_layout')

@section('main_content')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title">Job Alert Subscribers</h1>
                <a href="{{ route('admin.job-alerts', array_merge(request()->query(), ['export' => 'csv'])) }}"
                   class="btn btn-gst">
                    <i class="bi bi-download me-2"></i> Export CSV
                </a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-1 small text-uppercase">Confirmed</p>
                            <h3 class="mb-0">{{ number_format($confirmedCount) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-1 small text-uppercase">Awaiting confirmation</p>
                            <h3 class="mb-0">{{ number_format($pendingCount) }}</h3>
                        </div>
                    </div>
                </div>
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
                                                <span class="badge bg-success">Confirmed</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Pending</span>
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

                    @if($subscriptions->hasPages())
                        <div class="mt-3">
                            {{ $subscriptions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
