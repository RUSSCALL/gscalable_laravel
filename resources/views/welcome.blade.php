@extends('main_layout')

{{-- Give the hero image absolute loading priority: the browser starts fetching it
     as soon as the HTML <head> is parsed, before CSS/fonts/JS. --}}
@push('head')
  <link rel="preload" as="image" href="{{ asset('assets/img/hero-security-lock.png') }}" fetchpriority="high">
@endpush

@section('main_content')

<!-- ======= Hero Section ======= -->
<section id="hero-top" class="gst-hero">
  <div class="container">
    <div class="gst-hero-copy" data-aos="fade-up" data-aos-delay="100">
      <p class="gst-hero-eyebrow">Cybersecurity &amp; Technology</p>
      <h1>Cybersecurity Built to Scale.</h1>
      <p class="gst-hero-subtitle">GST delivers cybersecurity, cloud, and DevSecOps services to federal agencies and enterprise clients &mdash; engineered by a certified team.</p>
      <div class="gst-hero-actions">
        <a href="#capabilities" class="btn-gst-primary scrollto">Explore Capabilities</a>
        <a href="#contact" class="btn-gst-ghost scrollto">Contact Us</a>
      </div>
    </div>
    <div class="gst-hero-graphic-wrap">
      <img class="gst-hero-graphic" src="{{ asset('assets/img/hero-security-lock.png') }}"
           alt="" width="1500" height="844"
           loading="eager" fetchpriority="high">
    </div>
  </div>
</section><!-- End Hero -->

<main id="main">

  <!-- ======= Certification Marquee ======= -->
  @php
    $gstCertifications = [
      ['file' => 'cissp.png', 'label' => 'CISSP', 'alt' => '(ISC)² CISSP'],
      ['file' => 'cisa.png', 'label' => 'CISA', 'alt' => 'ISACA CISA'],
      ['file' => 'security-plus.png', 'label' => 'Security+', 'alt' => 'CompTIA Security+'],
      ['file' => 'nist-800-53.png', 'label' => 'RMF / NIST 800-53', 'alt' => 'NIST'],
      ['file' => 'fedramp.png', 'label' => 'FedRAMP', 'alt' => 'FedRAMP'],
      ['file' => 'cmmc.png', 'label' => 'CMMC', 'alt' => 'Cyber AB CMMC'],
      ['file' => 'iso-27001.png', 'label' => 'ISO 27001', 'alt' => 'ISO/IEC'],
      ['file' => 'pci-dss.png', 'label' => 'PCI DSS', 'alt' => 'PCI Security Standards Council'],
    ];
  @endphp
  <section id="credibility" class="cert-marquee-section" aria-labelledby="credibility-heading">
    <h2 id="credibility-heading" class="cert-marquee-heading">Certified Expertise</h2>

    <p class="visually-hidden" id="cert-marquee-list">
      Industry certifications and compliance frameworks our team holds:
      {{ collect($gstCertifications)->pluck('label')->implode(', ') }}.
    </p>

    {{-- 3 identical copies of the badge set. The track scrolls left by exactly
         one copy (-33.333%) then resets — because copy 2 lands where copy 1 was,
         the reset is invisible. Two copies always cover the viewport, so there
         is never a gap regardless of screen width. --}}
    <div class="cert-marquee" aria-hidden="true">
      <div class="cert-marquee-track">
        @for ($set = 0; $set < 3; $set++)
          @foreach ($gstCertifications as $cert)
            <span class="cert-badge">
              <img src="{{ asset('assets/img/certifications/' . $cert['file']) }}" alt="" height="26" loading="lazy" decoding="async">
            </span>
          @endforeach
        @endfor
      </div>
    </div>
  </section><!-- End Certification Marquee -->

  <!-- ======= Who We Are ======= -->
  <section id="about" class="about-lede" aria-labelledby="about-heading">
    <div class="about-lede-media">
      <img src="{{ asset('assets/img/who-we-are-2.jpg') }}" alt="" width="1200" height="801" loading="lazy" decoding="async" aria-hidden="true">
      <a href="{{ route('careers') }}" class="about-lede-join">
        <span class="about-lede-join-pulse" aria-hidden="true"></span>
        <span class="about-lede-join-label">Join Our Team <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
      </a>
    </div>
    <div class="container" data-aos="fade-up">
      <div class="about-lede-body">
        <div class="section-title">
          <h2 id="about-heading">Who We Are</h2>
          <p>Cybersecurity and Technology, Engineered with Precision</p>
        </div>
        <p class="about-lede-intro">GST is a cybersecurity and technology company delivering secure, scalable systems for federal agencies and enterprise clients. Our team holds industry-recognized credentials across risk management, compliance, and cloud security &mdash; and applies that expertise to every engagement, from assessment to operations.</p>
        <ul class="about-lede-principles">
          <li>
            <i class="bi bi-shield-check"></i>
            <div>
              <h4>Security-First Engineering</h4>
              <p>Every system we build or modernize is designed with security embedded from the start, not layered on afterward.</p>
            </div>
          </li>
          <li>
            <i class="bi bi-diagram-3"></i>
            <div>
              <h4>Built for Mission Scale</h4>
              <p>Our infrastructure and application work is engineered to perform reliably under real operational demand.</p>
            </div>
          </li>
          <li>
            <i class="bi bi-award"></i>
            <div>
              <h4>Certified Practitioners</h4>
              <p>Our specialists hold credentials spanning CISSP, CISA, Security+, and related compliance frameworks.</p>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </section><!-- End Who We Are -->

  <!-- ======= Capabilities ======= -->
  <section id="capabilities" class="capabilities section-bg" aria-labelledby="capabilities-heading">
    <div class="container" data-aos="fade-up">
      <div class="section-title">
        <h2 id="capabilities-heading">Capabilities</h2>
        <p>Secure Technology, End to End</p>
      </div>

      <div class="capability-grid" data-aos="fade-up" data-aos-delay="150">
        <div class="capability-card gst-card-base">
          <div class="capability-icon"><i class="bi bi-shield-lock"></i></div>
          <h3>Cybersecurity &amp; Compliance</h3>
          <p class="capability-framing">Continuous protection and compliance for high-value systems and sensitive data.</p>
          <div class="capability-tags">
            <span class="capability-tag">ATO Support</span>
            <span class="capability-tag">IDS / IPS</span>
            <span class="capability-tag">Penetration Testing</span>
            <span class="capability-tag">Cloud Security Assessment</span>
            <span class="capability-tag">RMF</span>
            <span class="capability-tag">CMMC</span>
            <span class="capability-tag">PCI DSS</span>
            <span class="capability-tag">Secure Coding</span>
          </div>
        </div>

        <div class="capability-card gst-card-base">
          <div class="capability-icon"><i class="bi bi-code-slash"></i></div>
          <h3>Software &amp; Application Engineering</h3>
          <p class="capability-framing">Applications engineered with security built in, not bolted on.</p>
          <div class="capability-tags">
            <span class="capability-tag">Custom Development</span>
            <span class="capability-tag">Stack Modernization</span>
            <span class="capability-tag">Rapid Prototyping</span>
            <span class="capability-tag">UX / UI Design</span>
            <span class="capability-tag">Web App Development</span>
            <span class="capability-tag">App Operations &amp; Maintenance</span>
            <span class="capability-tag">AI &amp; Machine Learning</span>
            <span class="capability-tag">Mobile Development</span>
          </div>
        </div>

        <div class="capability-card gst-card-base">
          <div class="capability-icon"><i class="bi bi-server"></i></div>
          <h3>Cloud &amp; Infrastructure</h3>
          <p class="capability-framing">Scalable, reliable infrastructure engineered for continuous delivery.</p>
          <div class="capability-tags">
            <span class="capability-tag">AWS</span>
            <span class="capability-tag">Azure</span>
            <span class="capability-tag">Google Cloud</span>
            <span class="capability-tag">Cloud Modernization</span>
            <span class="capability-tag">CI/CD Pipelines</span>
            <span class="capability-tag">DevSecOps</span>
            <span class="capability-tag">Microservices</span>
          </div>
        </div>
      </div>
    </div>
  </section><!-- End Capabilities -->

  <!-- ======= Contract Vehicles Teaser ======= -->
  <section id="cv-teaser" class="cv-teaser">
    <div class="container" data-aos="fade-up">
      <div class="cv-teaser-strip gst-card-base">
        <p><strong>Trusted by federal agencies nationwide</strong> &mdash; delivering cybersecurity, IT, and mission support to the organizations that depend on them.</p>
        <a href="{{ route('contract_vehicles') }}" class="btn-gst-primary">View Our Partnerships</a>
      </div>
    </div>
  </section><!-- End Contract Vehicles Teaser -->

  <!-- ======= GST Ecosystem ======= -->
  <section id="ecosystem" class="ecosystem section-bg" aria-labelledby="ecosystem-heading">
    <div class="container" data-aos="fade-up">
      <div class="section-title">
        <h2 id="ecosystem-heading">The GST Ecosystem</h2>
        <p>Specialized Partners Extending What We Do</p>
      </div>

      <div class="ecosystem-strip" data-aos="fade-up" data-aos-delay="150">
        <div class="ecosystem-card gst-card-base">
          <p class="ecosystem-kicker">Brand &amp; Growth Marketing</p>
          <h3>ScalePlus</h3>
          <p>Marketing and brand strategy for growing organizations.</p>
          <a href="https://scalepluspro.com" target="_blank" rel="noopener" class="ecosystem-link">Visit ScalePlus <i class="bi bi-box-arrow-up-right"></i></a>
        </div>

        <div class="ecosystem-card gst-card-base">
          <p class="ecosystem-kicker">Workforce Development</p>
          <h3>AdvanceMentor Academy</h3>
          <p>Building the cybersecurity and cloud talent pipeline through hands-on training, mentorship, and job placement support.</p>
          <a href="https://advancementor.academy" target="_blank" rel="noopener" class="ecosystem-link">Learn About AdvanceMentor Academy <i class="bi bi-box-arrow-up-right"></i></a>
        </div>
      </div>
    </div>
  </section><!-- End GST Ecosystem -->

  <!-- ======= CTA Band ======= -->
  <section class="cta-band" aria-labelledby="cta-heading">
    <div class="container" data-aos="fade-up">
      <h2 id="cta-heading">Talk to Our Security Team</h2>
      <p>Ready to discuss your cybersecurity or technology modernization needs?</p>
      <div class="cta-band-actions">
        <a href="#contact" class="btn-gst-primary scrollto">Contact Us</a>
      </div>
    </div>
  </section><!-- End CTA Band -->

  <!-- ======= Contact Section ======= -->
  <section id="contact" class="contact" aria-labelledby="contact-heading">
    <div class="container" data-aos="fade-up">

      <div class="section-title">
        <h2 id="contact-heading">Contact</h2>
        <p>Talk to Our Team</p>
      </div>

      <div class="row">
        <div class="col-lg-5">
          <div class="row">
            <div class="col-md-6 col-lg-12">
              <div class="info-box mt-4">
                <i class="bx bx-envelope"></i>
                <h3>Email Us</h3>
                <p><a href="mailto:reply@gscalabletech.com">reply@gscalabletech.com</a></p>
              </div>
            </div>
            <div class="col-md-6 col-lg-12">
              <div class="info-box mt-4">
                <i class="bx bx-phone-call"></i>
                <h3>Call Us</h3>
                <p><a href="tel:+12403198823">+1 240-319-8823</a></p>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <form id="gst-contact-form" class="gst-contact-form mt-4 mt-lg-0" novalidate>
            <div class="row">
              <div class="col-md-6">
                <label for="gst-contact-name">Your Name</label>
                <input type="text" id="gst-contact-name" name="name" required>
              </div>
              <div class="col-md-6">
                <label for="gst-contact-email">Your Email</label>
                <input type="email" id="gst-contact-email" name="email" required>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <label for="gst-contact-subject">Subject</label>
                <input type="text" id="gst-contact-subject" name="subject" required>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <label for="gst-contact-message">Message</label>
                <textarea id="gst-contact-message" name="message" rows="5" required></textarea>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn-gst-primary">Send Message</button>
                <p class="gst-contact-form-note">Opens in your email app, sent from your own address.</p>
              </div>
            </div>
          </form>
        </div>
      </div>

    </div>
  </section><!-- End Contact Section -->

</main><!-- End #main -->

@endsection
