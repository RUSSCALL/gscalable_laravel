  <!-- ======= Header ======= -->
  <header id="header" class="fixed-top">
    <div class="container d-flex align-items-center justify-content-between">

      <a href="{{ route('home') }}" class="logo"><img src="{{ asset('assets/img/GST-logo-white.png')}}" alt="Global Scalable Technologies" class="img-fluid"></a>

      <nav id="navbar" class="navbar" aria-label="Main navigation">
        <ul>
          <li><a class="nav-link scrollto {{ Route::currentRouteName() == 'home' ? 'active' : '' }}" href="{{ route('home') }}#hero-top" {{ Route::currentRouteName() == 'home' ? 'aria-current=page' : '' }}>Home</a></li>

          <li><a class="nav-link scrollto" href="{{ route('home') }}#capabilities">Capabilities</a></li>

          <li><a class="nav-link scrollto" href="{{ route('home') }}#ecosystem">Ecosystem</a></li>

          <li><a class="nav-link scrollto" href="{{ route('home') }}#contact">Contact</a></li>

          <li><a class="nav-link {{ Route::currentRouteName() == 'contract_vehicles' ? 'active' : '' }}" href="{{ route('contract_vehicles') }}" {{ Route::currentRouteName() == 'contract_vehicles' ? 'aria-current=page' : '' }}>Contract Vehicles</a></li>

          <li><a class="nav-link {{ Route::currentRouteName() == 'careers' ? 'active' : '' }}" href="{{ route('careers')}}" {{ Route::currentRouteName() == 'careers' ? 'aria-current=page' : '' }}>Careers</a></li>

          <li class="dropdown">
            <a class="nav-link" href="#" id="navbarDropdown" role="button" aria-haspopup="true" aria-expanded="false">
                GST Portal <i class="bi bi-chevron-down"></i>
            </a>
            <ul aria-labelledby="navbarDropdown">
                @guest
                    <li><a href="{{ route('login')}}"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</a></li>
                    <li><a href="{{ route('register')}}"><i class="bi bi-person-plus me-2"></i>Register</a></li>
                @else
                    <li><a href="{{ route('auth.redirect')}}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                    <li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="p-0">
                            @csrf
                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </form>
                    </li>
                @endguest
            </ul>
        </li>

      </ul>
        <button type="button" class="mobile-nav-toggle" aria-expanded="false" aria-controls="navbar" aria-label="Toggle navigation menu">
          <i class="bi bi-list" aria-hidden="true"></i>
        </button>
      </nav><!-- .navbar -->

    </div>
  </header><!-- End Header -->
