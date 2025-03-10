<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GST Password Reset</title>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Global Scalable Technologies (GST) - Password reset portal. Reset your password to regain access to GST's digital services, cybersecurity solutions, and cloud infrastructure management." name="description">
    <meta content="GST, Global Scalable Technologies, password reset, forgot password, account recovery, cybersecurity, cloud services, digital transformation, security solutions, IT services, enterprise technology" name="keywords">
  
    <!-- Favicons -->
    <link href="{{ asset('assets/img/Favicon1.jpg')}}" rel="icon">
    <link href="{{ asset('assets/img/Favicon1.jpg')}}" rel="apple-touch-icon">
    <link href="{{asset('assets/css/login_signup.css')}}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

</head>
<body>
    <div class="gst_login_container">
        <div class="gst_login_form_section">
            <div class="gst_login_logo">
                <a href="{{ route('home') }}"><img src="{{ asset('assets/img/GST-logo-white.png')}}" alt="GST Logo"></a>
            </div>
            
            <div class="gst_login_heading">
                <h1>Reset Your Password</h1>
                <p>Enter your email address and we'll send you a link to reset your password</p>
            </div>
            
            <form class="gst_login_form" method="POST" action="{{route('password.request')}}">
                @csrf
                @if (session('status'))
                    <div class="gst-success-alert">
                        <div class="gst-success-alert-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div class="gst-success-alert-content">
                            <p>{{ session('status') }}</p>
                        </div>
                        <button type="button" class="gst-success-alert-close" onclick="this.parentElement.style.display='none';">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                @endif
                <div class="gst_login_form_group">
                    <label class="gst_login_label">E-mail</label>
                    <input type="email" name="email" class="gst_login_input" placeholder="example@email.com" required>
                    @error('email')
                        <span class="gst-form-error-message">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
                </div>
                
                <button type="submit" class="gst_login_button">Send Reset Link</button>
            </form>
            
            <div class="gst_login_divider">Alternatively</div>
            
            
            <div class="gst_login_footer">
                <p>Don't have an account? <a href="{{route('register')}}">Create an account</a></p>
            </div>
        </div>
        
        <div class="gst_login_image_section">
            <div class="gst_login_image_overlay">
                <img src="{{ asset('assets/img/GST-Carousel-image.jpg')}}" alt="GST digital transformation solutions" loading="lazy" width="800" height="600">
            </div>
        </div>
    </div>
</body>
</html>