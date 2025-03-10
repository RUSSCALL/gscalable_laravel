<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email Address</title>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Global Scalable Technologies (GST) - Verify you email address to gain access to GST's digital services, cybersecurity solutions, and cloud infrastructure management." name="description">
    <meta content="GST, Global Scalable Technologies, verify email address,  cybersecurity, cloud services, digital transformation, security solutions, IT services, enterprise technology" name="keywords">
  
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
                <h1>Verify Your Email Address</h1>
                <p>Please Check Your Email For a Verification Link</p>
            </div>
            @if (session('status'))
            <div id="flash-message" class=" gst-success-alert alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            <form class="gst_login_form" method="POST" action="{{route('verification.send')}}">
                @csrf
                <button type="submit" class="gst_login_button">Resend Email</button>
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