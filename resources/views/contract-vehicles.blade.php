@extends('main_layout')

@section('main_content')

<!-- ======= Contract Vehicles Hero ======= -->
<section class="cv-hero section-bg">
    <div class="container" data-aos="fade-up">
        <h1 class="cv-hero-title">Contract Vehicles</h1>
        <p class="cv-hero-subtitle">
            Cybersecurity, IT, and mission support services delivered to federal agencies and private industry.
        </p>
    </div>
</section>

<div class="container">
    <hr class="gsa-divider">
</div>

<!-- ======= Trusted Government Partnerships ======= -->
<section id="trusted_partnerships" class="section-bg">
    <div class="container" data-aos="fade-up">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="section-heading">
                    <h2>TRUSTED GOVERNMENT PARTNERSHIPS</h2>
                </div>
                <div class="partnerships-statement">
                    <p>Global Scalable Technologies is proud to support government organizations in advancing secure, resilient, and mission-focused technology environments. Through our work with public-sector partners, we bring a commitment to cybersecurity, innovation, operational excellence, and dependable service delivery. The agencies represented below reflect organizations we have had the privilege of supporting as we help strengthen technology capabilities and enable critical missions.</p>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="partnerships-media">
                    {{-- Public domain flag (Wikimedia Commons); decorative, so alt is empty. --}}
                    <img src="{{ asset('assets/img/site/us-flag.png') }}" alt="" class="img-fluid" loading="lazy">
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <hr class="gsa-divider">
</div>

<!-- ======= Agencies & Partners Section ======= -->
<section id="partners" class="section-bg-alt">
    <div class="container" data-aos="fade-up">
        <div class="section-heading">
            <h2>AGENCIES &amp; PARTNERS</h2>
        </div>
        <div class="partners-grid">
            <div class="partner-col">
                <div class="partner-card">
                    <div class="partner-logo-wrap">
                        <img src="{{ asset('assets/img/partners/dhs.png') }}" alt="Department of Homeland Security" loading="lazy">
                    </div>
                    <p class="partner-name">Department of Homeland Security</p>
                </div>
            </div>
            <div class="partner-col">
                <div class="partner-card">
                    <div class="partner-logo-wrap">
                        <img src="{{ asset('assets/img/partners/uscg.png') }}" alt="United States Coast Guard" loading="lazy">
                    </div>
                    <p class="partner-name">U.S. Coast Guard</p>
                </div>
            </div>
            <div class="partner-col">
                <div class="partner-card">
                    <div class="partner-logo-wrap">
                        <img src="{{ asset('assets/img/partners/usaf.png') }}" alt="United States Air Force" loading="lazy">
                    </div>
                    <p class="partner-name">U.S. Air Force</p>
                </div>
            </div>
            <div class="partner-col">
                <div class="partner-card">
                    <div class="partner-logo-wrap">
                        <img src="{{ asset('assets/img/partners/education.png') }}" alt="Department of Education" loading="lazy">
                    </div>
                    <p class="partner-name">Department of Education</p>
                </div>
            </div>
            <div class="partner-col">
                <div class="partner-card">
                    <div class="partner-logo-wrap">
                        <img src="{{ asset('assets/img/partners/va.png') }}" alt="Department of Veterans Affairs" loading="lazy">
                    </div>
                    <p class="partner-name">Department of Veterans Affairs</p>
                </div>
            </div>
            <div class="partner-col">
                <div class="partner-card">
                    <div class="partner-logo-wrap">
                        <span class="partner-wordmark partner-wordmark--maryland"><span class="partner-wordmark-kicker">State of</span>Maryland</span>
                    </div>
                    <p class="partner-name">State of Maryland</p>
                </div>
            </div>
            <div class="partner-col">
                <div class="partner-card">
                    <div class="partner-logo-wrap">
                        <span class="partner-wordmark partner-wordmark--uscis">USCIS</span>
                    </div>
                    <p class="partner-name">U.S. Citizenship &amp; Immigration Services</p>
                </div>
            </div>
            <div class="partner-col">
                <div class="partner-card">
                    <div class="partner-logo-wrap">
                        <span class="partner-wordmark partner-wordmark--aws">AWS<span class="partner-wordmark-sub">Partner</span></span>
                    </div>
                    <p class="partner-name">AWS Partner</p>
                </div>
            </div>
            <div class="partner-col">
                <div class="partner-card">
                    <div class="partner-logo-wrap">
                        <span class="partner-wordmark partner-wordmark--google">Google</span>
                    </div>
                    <p class="partner-name">Cloud &amp; Workspace</p>
                </div>
            </div>
            <div class="partner-col">
                <div class="partner-card">
                    <div class="partner-logo-wrap">
                        <img src="{{ asset('assets/img/partners/cisco.png') }}" alt="Cisco" loading="lazy">
                    </div>
                    <p class="partner-name">Cisco</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <hr class="gsa-divider">
</div>

@endsection
