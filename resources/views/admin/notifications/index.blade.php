@extends('admin.admin_layout')

@section('main_content')
    <div class="main-content">
        <div class="container-fluid p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <h1 class="page-title">Notifications</h1>
                @if($notifications->total())
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-check2-all me-1"></i> Mark all as read</button>
                        </form>
                        <form method="POST" action="{{ route('admin.notifications.clear') }}" onsubmit="return confirm('Clear all notifications? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Clear all</button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="list-group list-group-flush">
                    @forelse($notifications as $notification)
                        <form method="POST" action="{{ route('admin.notifications.open', $notification->id) }}">
                            @csrf
                            <button type="submit" @class(['list-group-item list-group-item-action d-flex gap-3 align-items-start', 'notification-item is-unread' => ! $notification->read_at])>
                                <i class="bi {{ $notification->data['icon'] ?? 'bi-bell' }} fs-5"></i>
                                <span class="flex-grow-1 text-start">
                                    <span class="d-block">{{ $notification->data['message'] ?? $notification->data['title'] ?? 'Notification' }}</span>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </span>
                                @unless($notification->read_at)<span class="badge bg-danger">New</span>@endunless
                            </button>
                        </form>
                    @empty
                        <div class="list-group-item text-center text-muted py-5">You're all caught up.</div>
                    @endforelse
                </div>
            </div>

            @if($notifications->hasPages())
                <div class="mt-3">{{ $notifications->links() }}</div>
            @endif
        </div>
    </div>
@endsection
