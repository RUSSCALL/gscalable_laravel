<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GST Admin Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ \App\Support\VersionedAsset::url('assets/css/admin_dashboard.css') }}">
    @stack('head')
</head>
            <!-- Flash Notification JS -->
            @component('components.notification_flash')
            @endcomponent
<body>

    <main>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <a class="navbar-brand d-lg-none" href="{{route('home')}}">
                    <img src="{{ asset('assets/img/GST-logo-white.png')}}" alt="GST Logo" class="img-fluid" style="max-height: 60px;">
                </a>
                <button class="navbar-toggler toggle-sidebar" type="button">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                               aria-label="Notifications{{ $unreadNotificationCount ? ', ' . $unreadNotificationCount . ' unread' : '' }}">
                                <i class="bi bi-bell"></i>
                                @if($unreadNotificationCount)
                                    <span class="badge rounded-pill bg-danger">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="navbarDropdown">
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
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->name ?? 'Admin' }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.settings') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                        @csrf
                                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-logo">
                <a href="{{route('home')}}"><img src="{{ asset('assets/img/GST-logo-white.png')}}" alt="GST Logo" class="img-fluid"></a>
            </div>
            <ul class="sidebar-menu mt-4">
                <li class="sidebar-item">
                    <a href="{{ route('AdminDashboard') }}" class="sidebar-link {{ Route::is('AdminDashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('jobListings') }}" class="sidebar-link {{ Route::is('jobListings', 'jobs.*') ? 'active' : '' }}">
                        <i class="bi bi-briefcase"></i> Job Listings
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.job-alerts') }}" class="sidebar-link {{ Route::is('admin.job-alerts') ? 'active' : '' }}">
                        <i class="bi bi-bell"></i> Job Alerts
                    </a>
                </li>
                @can('user-is-superadmin')
                    <li class="sidebar-item">
                        <a href="{{ route('admin.roles') }}" class="sidebar-link {{ Route::is('admin.roles') ? 'active' : '' }}">
                            <i class="bi bi-shield-lock"></i> Roles
                        </a>
                    </li>
                @endcan
        
                <li class="sidebar-item">
                    <a href="{{ route('admin.applicants.index') }}" class="sidebar-link {{ Route::is('admin.applicants.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Applicants
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.messages.index') }}" class="sidebar-link {{ Route::is('admin.messages.*') ? 'active' : '' }}">
                        <i class="bi bi-chat-left-text"></i> Messages
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.interviews.index') }}" class="sidebar-link {{ Route::is('admin.interviews.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event"></i> Interviews
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.onboarding.index') }}" class="sidebar-link {{ Route::is('admin.onboarding.*') ? 'active' : '' }}">
                        <i class="bi bi-check2-circle"></i> Onboarding
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.reports.index') }}" class="sidebar-link {{ Route::is('admin.reports.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart"></i> Reports
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.settings') }}" class="sidebar-link {{ Route::is('admin.settings') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </li>
            </ul>
        </div>
    </main>


        @yield('main_content')

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    	<!-- Flash-Notifcation js -->
	@component('components.notification_flash_js')
	@endcomponent
    <script src="{{ \App\Support\VersionedAsset::url('assets/js/admin_dashboard.js') }}"></script>
    @yield('scripts')
</body>
</html>