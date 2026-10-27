@extends('admin.admin_layout')

@section('title', 'Notifications')

@section('main_content')
    @component('admin._page_header', ['eyebrow' => 'Account', 'title' => 'Notifications'])
        @if($notifications->total())
            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-check2-all me-1"></i> Mark all as read</button>
            </form>
            <form method="POST" action="{{ route('admin.notifications.clear') }}" onsubmit="return confirm('Clear all notifications? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Clear all</button>
            </form>
        @endif
    @endcomponent

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
                        @unless($notification->read_at)<span class="badge-new">New</span>@endunless
                    </button>
                </form>
            @empty
                <div class="empty-state">
                    <span class="empty-state-icon"><i class="bi bi-bell-slash"></i></span>
                    <h3>You're all caught up.</h3>
                    <p>New applications and jobs that are about to close show up here.</p>
                </div>
            @endforelse
        </div>
        @if($notifications->hasPages())
            <div class="card-footer">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
