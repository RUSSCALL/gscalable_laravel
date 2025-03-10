<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Global Scalable Technologies</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="{{ asset('assets/img/Favicon1.jpg')}}" rel="icon">
  <link href="{{ asset('assets/img/Favicon1.jpg')}}" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/aos/aos.css')}}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css')}}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/remixicon/remixicon.css')}}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css')}}" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{asset('assets/css/style.css')}}" rel="stylesheet">
</head>
            <!-- Flash Notification JS -->
            @component('components.notification_flash')
            @endcomponent
<body>

  <!-- ======= Header ======= -->
  <header id="header" class="fixed-top ">
    <div class="container d-flex align-items-center justify-content-between">

      <a href="/" class="logo"><img src="{{ asset('assets/img/GST-logo-white.png')}}" alt="" class="img-fluid"></a>

      <nav id="navbar" class="navbar">
        <ul>
          <li><a class="nav-link scrollto {{ Route::currentRouteName() == 'home' ? 'active' : '' }}" href="{{ route('home') }}">Home</a></li>
          
          {{-- <li><a class="nav-link scrollto" href="{{ route('home') }}#about">About</a></li> --}}
          
          <li><a class="nav-link scrollto" href="{{ route('home') }}#services">Services</a></li>
          
          <li><a class="nav-link scrollto" href="{{ route('home') }}#contact">Contact</a></li>
          
          <li><a class="nav-link {{ Route::currentRouteName() == 'contract_vehicles' ? 'active' : '' }}" href="{{ route('contract_vehicles') }}">Contract Vehicles</a></li>
          
          <li><a class="nav-link" href="{{ route('careers')}}">Careers</a></li>

          <li class="dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                GST Portal
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                @guest
                    <li><a class="dropdown-item" href="{{ route('login')}}"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</a></li>
                    <li><a class="dropdown-item" href="{{ route('register')}}"><i class="bi bi-person-plus me-2"></i>Register</a></li>
                @else
                    <li><a class="dropdown-item" href="{{ route('auth.redirect')}}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                    <li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="dropdown-item p-0">
                            @csrf
                            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </form>
                    </li>
                @endguest
            </ul>
        </li>
          
      </ul>
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav><!-- .navbar -->

    </div>
  </header><!-- End Header -->

  @yield('main_content')
  
  <!-- ======= Footer ======= -->
  <footer id="footer">
    <div class="footer-top">
      <div class="container">
        <div class="row">

          <div class="col-lg-3 col-md-6">
            <div class="footer-info">
              <h3>Global Scalable Technologies</h3>
              <p>
                <strong>Phone: </strong>+1 240-319-8823<br>
                <strong>Email: </strong> support@gscalable.com<br>
              </p>
              <div class="social-links mt-3">
                <a href="#" class="twitter"><i class="bx bxl-twitter"></i></a>
                <a href="#" class="instagram"><i class="bx bxl-instagram"></i></a>
                <a href="#" class="linkedin"><i class="bx bxl-linkedin"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-6 footer-links">
            <h4>Useful Links</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Home</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">About us</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Services</a></li>
              </ul>
          </div>

        </div>
      </div>
    </div>

    <div class="container">
      <div class="copyright">
        &copy; Copyright <strong><span>Global Scalable Technologies</span></strong>. All Rights Reserved
      </div>
    </div>
  </footer><!-- End Footer -->

  <div id="preloader"></div>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/purecounter/purecounter_vanilla.js')}}"></script>
  <script src="{{ asset('assets/vendor/aos/aos.js')}}"></script>
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{ asset('assets/vendor/glightbox/js/glightbox.min.js')}}"></script>
  <script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js')}}"></script>
  <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js')}}"></script>
  <script src="{{ asset('assets/vendor/php-email-form/validate.js')}}"></script>

  <!-- Template Main JS File -->
  <script src="{{ asset('assets/js/main.js')}}"></script>
            <!-- Flash Notification JS -->
            @component('components.notification_flash_js')
            @endcomponent
</body>

</html>