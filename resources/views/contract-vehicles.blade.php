@extends('main_layout')

@section('main_content')

<!-- ======= Contract Vehicles Hero ======= -->
<section class="cv-hero section-bg">
    <div class="container" data-aos="fade-up">
        <h1 class="cv-hero-title">Contract Vehicles</h1>
        <p class="cv-hero-subtitle">
            Global Scalable Technologies (GST) delivers cybersecurity, IT, and mission support services to federal
            agencies and private industry through the contract vehicles below. These partnerships give agencies
            fast, pre-vetted access to our teams and capabilities.
        </p>
    </div>
</section>

<div class="container">
    <hr class="gsa-divider">
</div>

<!-- ======= GSA HACS Services Section ======= -->
<section id="gsa_hacs" class="section-bg">
    <div class="container" data-aos="fade-up">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="section-heading">
                    <h2>GSA HIGHLY ADAPTIVE CYBERSECURITY SERVICES (HACS), SPECIAL ITEM NUMBER (SIN) 54151</h2>
                </div>
                <ul class="services-list">
                    <li><i class="bi bi-check-circle"></i> High-Value Asset Assessments</li>
                    <li><i class="bi bi-check-circle"></i> Risk and Vulnerability Assessment</li>
                    <li><i class="bi bi-check-circle"></i> Cyber Hunt</li>
                    <li><i class="bi bi-check-circle"></i> Incident Response</li>
                    <li><i class="bi bi-check-circle"></i> Penetration Testing</li>
                </ul>
            </div>
            <div class="col-lg-4">
                <div class="gsa-logo">
                    <img src="{{ asset('assets/img/partners/gsa.png') }}" alt="GSA Logo" class="img-fluid" loading="lazy">
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="services-description">
                    <p>This HACS SIN, available through the Information Technology Category (ITC) under GSA's Multiple Award Schedule (MAS), provides agencies quicker access to key, pre-vetted support services that will expand agencies' capacity to test their high-priority IT systems, rapidly address potential vulnerabilities, and stop adversaries before they impact our networks.</p>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-12 text-center">
                <a href="#" class="btn btn-primary">VIEW MAS PRICE LIST</a>
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
            <h2>AGENCIES &amp; PARTNERS WE SUPPORT</h2>
        </div>
        <div class="partners-intro">
            <p>GST proudly supports federal agencies and private-sector leaders under the contract vehicles above, delivering cybersecurity and IT services to the following organizations.</p>
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
