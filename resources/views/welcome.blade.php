@extends('main_layout')

@section('main_content')
      <!-- ======= Hero Section ======= -->
  <section id="hero">
    <div class="hero-container" data-aos="fade-up" data-aos-delay="150">
      <h1>Secure. Innovate. Scale.</h1>
      <h2>Your Partner in Digital Transformation with GST</h2>
      <div class="d-flex">
        <a href="#about" class="btn-get-started scrollto">See More</a>
        <a href="https://www.youtube.com/" class="glightbox btn-watch-video"><i class="bi bi-play-circle"></i><span>Watch Video</span></a>
      </div>
    </div>
  </section><!-- End Hero -->

  <main id="main">

    <!-- ======= About Section ======= -->
    <section id="about" class="about">
      <div class="container" data-aos="fade-up">

        <div class="row justify-content-end">
          <div class="col-lg-11">
            <div class="row justify-content-end">

              <div class="col-lg-3 col-md-5 col-6 d-md-flex align-items-md-stretch">
                <div class="count-box">
                  <i class="bi bi-emoji-smile"></i>
                  <span data-purecounter-start="0" data-purecounter-end="125" data-purecounter-duration="1" class="purecounter"></span>
                  <p>Happy Clients</p>
                </div>
              </div>

              <div class="col-lg-3 col-md-5 col-6 d-md-flex align-items-md-stretch">
                <div class="count-box">
                  <i class="bi bi-journal-richtext"></i>
                  <span data-purecounter-start="0" data-purecounter-end="85" data-purecounter-duration="1" class="purecounter"></span>
                  <p>Projects</p>
                </div>
              </div>

              <div class="col-lg-3 col-md-5 col-6 d-md-flex align-items-md-stretch">
                <div class="count-box">
                  <i class="bi bi-clock"></i>
                  <span data-purecounter-start="0" data-purecounter-end="35" data-purecounter-duration="1" class="purecounter"></span>
                  <p>Years of experience</p>
                </div>
              </div>

              <div class="col-lg-3 col-md-5 col-6 d-md-flex align-items-md-stretch">
                <div class="count-box">
                  <i class="bi bi-award"></i>
                  <span data-purecounter-start="0" data-purecounter-end="48" data-purecounter-duration="1" class="purecounter"></span>
                  <p>Awards</p>
                </div>
              </div>

            </div>
          </div>
        </div>

        <div class="row">

          <div class="col-lg-6 video-box align-self-baseline" data-aos="zoom-in" data-aos-delay="100">
            <img src="assets/img/about.jpg" class="img-fluid" alt="">
            <a href="https://www.youtube.com/watch?v=jDDaplaOz7Q" class="glightbox play-btn mb-4"></a>
          </div>

          <div class="col-lg-6 pt-3 pt-lg-0 content">
            <h3>Secure Your Digital World with GST</h3>
            <p class="fst-italic">
              GST is a parent company that is dedicated to providing top-notch digital solutions to businesses of all sizes.
            </p>
            <ul>
              <li><i class="bx bx-check-double"></i> Continuous and Ultimate delivery of ATO, Intrusion Detection and Prevention Systems Implementation, Penetration Testing, Cloud Security Assessment and more with our Cybersecurity services.</li>
              <li><i class="bx bx-check-double"></i> Full Stack, CI/CD Pipeline, AI & Machine Learning, DevSecOps, Mobile App Development, and Microservices with our DevOps services.</li>
              <li><i class="bx bx-check-double"></i> Scalable infrastructure solutions with AWS Cloud, Azure Cloud, and Google Cloud.</li>
              <li><i class="bx bx-check-double"></i> Empower your digital career with our Advancementor Academy, offering Courses on Risk Management Framework, CMMC Compliance, ISO 27001, MS Office and Cloud Computing, Mentorship with industry experts, and Job Search assistance.</li>
            </ul>
            <p>
              At GST, we believe in providing comprehensive digital solutions to help businesses thrive in the digital world. Our team of talented designers, security experts, and DevOps professionals are dedicated to helping you Plan, Launch, and Grow your business.
            </p>
          </div>

        </div>

      </div>
    </section><!-- End About Section -->

    <!-- ======= About Boxes Section ======= -->
    <section id="about-boxes" class="about-boxes">
      <div class="container" data-aos="fade-up">
        <div class="row">
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
              <img src="{{ asset('assets/img/mission.jpg')}}" class="card-img-top" alt="...">
              <div class="card-icon">
                <i class="ri-flag-2-fill"></i>
              </div>
              <div class="card-body">
                <h5 class="card-title"><a href="">Our Mission</a></h5>
                <p class="card-text">GST is a parent company that is dedicated to providing top-notch digital solutions to businesses of all sizes.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="200">
            <div class="card">
              <img src="{{ asset('assets/img/plan.jpg')}}" class="card-img-top" alt="...">
              <div class="card-icon">
                <i class="ri-calendar-check-line"></i>
              </div>
              <div class="card-body">
                <h5 class="card-title"><a href="">Our Plan</a></h5>
                <p class="card-text">Secure your digital world with our top-notch cybersecurity services, DevOps services and infrastructure solutions. Empower your digital career with our Advancementor Academy.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="300">
            <div class="card">
              <img src="{{ asset('assets/img/vision.jpg')}}" class="card-img-top" alt="...">
              <div class="card-icon">
                <i class="ri-focus-2-line"></i>
              </div>
              <div class="card-body">
                <h5 class="card-title"><a href="">Our Vision</a></h5>
                <p class="card-text">To revolutionize the digital world by providing top-quality digital solutions and education to all businesses and individuals.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End About Boxes Section -->

    <!-- </section>End Features Section -->

    <!-- ======= Services Section ======= -->
    <section id="services" class="services section-bg">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>Services</h2>
          <p>Check our Services</p>
        </div>

        <div class="row" data-aos="fade-up" data-aos-delay="200">
          <div class="col-md-6">
            <div class="icon-box">
              <i class="bi bi-shield-lock"></i>
              <h4><a href="#">Cybersecurity</a></h4>
              <p>Continuous and Ultimate delivery of ATO, Intrusion Detection Systems Implementation, Intrusion Prevention Systems Implementation, Penetration Testing, Cloud Security Assessment, RMF compliance, PCI DSS compliance, CMMC compliance, PHI protection and compliance</p>
            </div>
          </div>
          <div class="col-md-6 mt-4 mt-md-0">
            <div class="icon-box">
              <i class="bi bi-laptop"></i>
              <h4><a href="#">Devops</a></h4>
              <p>Our team provides expert assistance with full stack development, CI/CD pipeline, AI and machine learning, DevSecOps, mobile app development, and microservices. We work closely with clients to achieve optimal business outcomes.</p>       
            </div>
          </div>
          <div class="col-md-6 mt-4 mt-md-0">
            <div class="icon-box">
              <i class="bi bi-server"></i>
              <h4><a href="#">Infrastructure</a></h4>
              <p>Our cloud services on Amazon Web Services (AWS), Microsoft Azure Cloud, and Google Cloud assist in building and managing a reliable and scalable infrastructure for your applications and services.</p>
            </div>
          </div>
          <div class="col-md-6 mt-4 mt-md-0">
            <div class="icon-box">
              <i class="bi bi-binoculars"></i>
              <h4><a href="https://advancementor.academy/" target="blank">Advancementor Academy</a></h4>
              <p>Advancementor Academy offers courses including RMF, CMMC, FedRamp, ISO 27001, MS office, AWS, and real-life projects. Get mentorship, job search guidance, 1:1 sessions, resume and interview guidance to be 99.99% job-ready.</p>
            </div>
          </div>
        </div>

      </div>
    </section><!-- End Services Section -->


    <!-- ======= Testimonials Section ======= -->
    <section id="testimonials" class="testimonials">
      <div class="container" data-aos="zoom-in">

        <div class="testimonials-slider swiper" data-aos="fade-up" data-aos-delay="100">
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="{{ asset('assets/img/testimonials/testimonials-1.jpg')}}" class="testimonial-img" alt="">
                <h3>Saul Goodman</h3>
                <h4>Ceo &amp; Founder</h4>
                <p>
                  <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                  I am so grateful to this company for helping me get my business off the ground. Their team is knowledgeable, responsive, and truly cares about their clients' success. They went above and beyond to help me achieve my goals.                  
                  <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="{{ asset('assets/img/testimonials/testimonials-2.jpg')}}" class="testimonial-img" alt="">
                <h3>Sara Wilsson</h3>
                <h4>Designer</h4>
                <p>
                  <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                  We were impressed with the professionalism and expertise of this company. They took the time to understand our unique needs and delivered a solution that exceeded our expectations. We look forward to working with them again.                  
                  <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="{{ asset('assets/img/testimonials/testimonials-3.jpg')}}" class="testimonial-img" alt="">
                <h3>Jena Karlis</h3>
                <h4>Store Owner</h4>
                <p>
                  <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                  We are thrilled with the results of our collaboration with this company. Their expertise and professionalism helped us achieve our business goals in a timely manner. We highly recommend them.                  
                  <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="{{ asset('assets/img/testimonials/testimonials-4.jpg')}}" class="testimonial-img" alt="">
                <h3>Matt Brandon</h3>
                <h4>Freelancer</h4>
                <p>
                  <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                  We were very pleased with the service provided by this company. They were knowledgeable, responsive, and delivered great results. Would definitely recommend them to others.                  <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="{{ asset('assets/img/testimonials/testimonials-5.jpg')}}" class="testimonial-img" alt="">
                <h3>John Larson</h3>
                <h4>Entrepreneur</h4>
                <p>
                  <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                  Working with this team has been an absolute pleasure. They are true professionals who go above and beyond to deliver outstanding results. Their attention to detail, responsiveness, and strategic thinking have been instrumental in driving the success of our marketing campaigns.                  
                  <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

          </div>
          <div class="swiper-pagination"></div>
        </div>

      </div>
    </section><!-- End Testimonials Section -->


    <!-- ======= Contact Section ======= -->
    <section id="contact" class="contact">
      <div class="container" data-aos="fade-up">

    <div class=" section-title">
        <h2>Contact</h2>
        <p>Contact Us</p>
      </div>

      <div class="row">

        <div class="col-lg-6">

          <div class="row">
            <div class="col-md-6">
              <div class="info-box mt-4">
                <i class="bx bx-envelope"></i>
                <h3>Email Us</h3>
                <p>support@gscalable.com</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-box mt-4">
                <i class="bx bx-phone-call"></i>
                <h3>Call Us</h3>
                <p>+1 240-319-882</p>
              </div>
            </div>
          </div>

        </div>

      </div>

      </div>
    </section><!-- End Contact Section -->

  </main><!-- End #main -->

@endsection