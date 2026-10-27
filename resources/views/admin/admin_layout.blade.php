@php
    $userName = auth()->user()->name ?? 'Admin';
    $initials = collect(preg_split('/\s+/', trim($userName)))->filter()->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');

    // [route, active route patterns, icon, label], grouped under sidebar headings.
    $navigation = [
        '' => [
            ['AdminDashboard', ['AdminDashboard'], 'bi-speedometer2', 'Dashboard'],
        ],
        'Hiring' => [
            ['jobListings', ['jobListings', 'jobs.*'], 'bi-briefcase', 'Job Listings'],
            ['admin.applicants.index', ['admin.applicants.*'], 'bi-people', 'Applicants'],
            ['admin.interviews.index', ['admin.interviews.*'], 'bi-calendar-event', 'Interviews'],
            ['admin.onboarding.index', ['admin.onboarding.*'], 'bi-check2-circle', 'Onboarding'],
            ['admin.messages.index', ['admin.messages.*'], 'bi-chat-left-text', 'Messages'],
            ['admin.job-alerts', ['admin.job-alerts'], 'bi-bell', 'Job Alerts'],
        ],
        'Admin' => array_filter([
            ['admin.reports.index', ['admin.reports.*'], 'bi-bar-chart', 'Reports'],
            Gate::allows('user-is-superadmin') ? ['admin.roles', ['admin.roles'], 'bi-shield-lock', 'Roles'] : null,
            ['admin.settings', ['admin.settings'], 'bi-gear', 'Settings'],
        ]),
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') &middot; GST Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    @stack('head')
    <link href="{{ \App\Support\VersionedAsset::url('assets/css/admin.css') }}" rel="stylesheet">
</head>
<body>
    @component('components.notification_flash')
    @endcomponent

    <aside class="sidebar" aria-label="Admin navigation">
        <div class="sidebar-brand">
            <a href="{{ route('AdminDashboard') }}"><img src="{{ asset('assets/img/GST-logo-white.png') }}" alt="GST Admin dashboard"></a>
        </div>

        @foreach($navigation as $section => $links)
            @if($section)
                <p class="sidebar-section">{{ $section }}</p>
            @endif
            <ul @class(['sidebar-menu', 'mt-3' => ! $section])>
                @foreach($links as [$route, $patterns, $icon, $label])
                    @php $active = Route::is(...$patterns); @endphp
                    <li class="sidebar-item">
                        <a href="{{ route($route) }}" @class(['sidebar-link', 'active' => $active]) @if($active) aria-current="page" @endif>
                            <i class="bi {{ $icon }}"></i> {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach

        <div class="sidebar-foot">
            <a href="{{ route('home') }}"><i class="bi bi-box-arrow-up-right me-2"></i>View public site</a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button type="button" class="topbar-icon-btn toggle-sidebar" aria-label="Open navigation">
                <i class="bi bi-list"></i>
            </button>
            <p class="topbar-crumbs">
                <a href="{{ route('AdminDashboard') }}">GST Admin</a>
                @hasSection('crumb_parent')
                    <i class="bi bi-chevron-right"></i> @yield('crumb_parent')
                @endif
                <i class="bi bi-chevron-right"></i> <strong>@yield('title', 'Dashboard')</strong>
            </p>

            <div class="topbar-actions">
                <div class="dropdown">
                    <button type="button" class="topbar-icon-btn" data-bs-toggle="dropdown" aria-expanded="false"
                            aria-label="Notifications{{ $unreadNotificationCount ? ', ' . $unreadNotificationCount . ' unread' : '' }}">
                        <i class="bi bi-bell"></i>
                        @if($unreadNotificationCount)
                            <span class="topbar-count">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end notification-menu">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        @forelse($recentNotifications as $notification)
                            <li>
                                <form method="POST" action="{{ route('admin.notifications.open', $notification->id) }}">
                                    @csrf
                                    <button type="submit" @class(['dropdown-item notification-item', 'is-unread' => ! $notification->read_at])>
                                        <i class="bi {{ $notification->data['icon'] ?? 'bi-bell' }} me-2"></i>
                                        <span>
                                            <span class="d-block">{{ $notification->data['message'] ?? $notification->data['title'] ?? 'Notification' }}</span>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </span>
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li><span class="dropdown-item-text text-muted">No notifications</span></li>
                        @endforelse
                        <li><hr class="dropdown-divider"></li>
                        @if($unreadNotificationCount)
                            <li>
                                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-check2-all me-2"></i>Mark all as read</button>
                                </form>
                            </li>
                        @endif
                        <li><a class="dropdown-item" href="{{ route('admin.notifications.index') }}"><i class="bi bi-list-ul me-2"></i>View all notifications</a></li>
                    </ul>
                </div>

                <div class="dropdown">
                    <button type="button" class="topbar-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="topbar-avatar" aria-hidden="true">{{ $initials }}</span>
                        <span class="topbar-user-name">{{ $userName }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="main-content">
            <div class="container-fluid p-4">
                @yield('main_content')
            </div>
        </main>

        <footer class="footer">&copy;{{ date('Y') }} Global Scalable Technologies (GST). All rights reserved.</footer>
    </div>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    @component('components.notification_flash_js')
    @endcomponent
    <script src="{{ \App\Support\VersionedAsset::url('assets/js/admin_dashboard.js') }}"></script>
    @yield('scripts')
</body>
</html>
