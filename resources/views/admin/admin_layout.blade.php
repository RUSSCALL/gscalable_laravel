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
    <link rel="stylesheet" type="text/css" href="{{'assets/css/admin_dashboard.css'}}">
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
                            <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell"></i>
                                <span class="badge rounded-pill bg-danger">3</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="#">New application received</a></li>
                                <li><a class="dropdown-item" href="#">Job posting expiring soon</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">View all notifications</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> Admin
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
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
                    <a href="{{ route('jobListings') }}" class="sidebar-link {{ Route::is('jobListings') ? 'active' : '' }}">
                        <i class="bi bi-briefcase"></i> Job Listings
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.job-alerts') }}" class="sidebar-link {{ Route::is('admin.job-alerts') ? 'active' : '' }}">
                        <i class="bi bi-bell"></i> Job Alerts
                    </a>
                </li>
        
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-people"></i> Applicants
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-chat-left-text"></i> Messages
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-calendar-event"></i> Interviews
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-check2-circle"></i> Onboarding
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-bar-chart"></i> Reports
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
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
    <script src="{{ asset('assets/js/admin_dashboard.js')}}"></script>
</body>
</html>