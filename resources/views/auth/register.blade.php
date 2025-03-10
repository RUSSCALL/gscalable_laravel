<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GST Sign Up</title>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Create your account with Global Scalable Technologies (GST) - Join our platform to access digital services, cybersecurity solutions, and cloud infrastructure management." name="description">
    <meta content="GST, Global Scalable Technologies, signup, register, create account, cybersecurity, cloud services, digital transformation, security solutions, IT services, enterprise technology" name="keywords">
  
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
                <h1>Create Your GST Account</h1>
                <p>Fill in your details to create a GST Portal account</p>
            </div>
            
            <form class="gst_login_form" method="POST" action="{{route('register')}}">
                @csrf

                <div class="gst_login_form_group">
                    <label class="gst_login_label">Full Name</label>
                    <input type="text" name="name" class="gst_login_input" placeholder="John Doe" required>
                    @error('name')
                        <span class="gst-form-error-message">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
                </div>
                
                <div class="gst_login_form_group">
                    <label class="gst_login_label">E-mail</label>
                    <input type="email" name="email" class="gst_login_input" placeholder="example@email.com" required>
                    @error('email')
                        <span class="gst-form-error-message">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
                </div>
                
                <div class="gst_login_form_group">
                    <label class="gst_login_label">Password</label>
                    <div class="gst_login_password_input_wrapper">
                        <input type="password" name="password"  class="gst_login_input" placeholder="••••••••" required>
                        <span class="gst_login_eye_icon">👁️</span>
                    @error('password')
                        <span class="gst-form-error-message">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
                    </div>
                </div>
                
                <div class="gst_login_form_group">
                    <label class="gst_login_label">Confirm Password</label>
                    <div class="gst_login_password_input_wrapper">
                        <input type="password" name="password_confirmation" class="gst_login_input" placeholder="••••••••" required>
                        <span class="gst_login_eye_icon">👁️</span>

                    </div>
                </div>
                
                <div class="gst_login_form_group">
                    <input type="checkbox" id="terms" required>
                    <label for="terms" class="gst_terms_label">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                </div>
                
                <button type="submit" class="gst_login_button">Create Account</button>
            </form>
            
            <div class="gst_login_divider">Alternatively</div>
            
            <div class="gst_login_footer">
                <p>Already have an account? <a href="{{route('login')}}">Sign in</a></p>
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