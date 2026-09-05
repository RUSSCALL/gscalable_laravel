  <!-- ======= Footer ======= -->
  <footer id="footer" class="site-footer">

    <div class="footer-globe-band">
      <canvas id="footerGlobeCanvas" aria-hidden="true"></canvas>
      <div class="footer-globe-fallback" aria-hidden="true"></div>
    </div>

    <div class="footer-top">
      <div class="container">
        <div class="row">

          <div class="col-lg-3 col-md-6">
            <div class="footer-info">
              <h3>Global Scalable Technologies</h3>
              <p>Cybersecurity and technology services engineered for mission-critical environments.</p>
              <p>
                <strong>Phone: </strong>+1 240-319-8823<br>
                <strong>Email: </strong> reply@gscalabletech.com<br>
              </p>
              <div class="social-links mt-3">
                <a href="https://www.linkedin.com/company/global-scalable-technologies" class="linkedin" target="_blank" rel="noopener" aria-label="GST on LinkedIn"><i class="bx bxl-linkedin"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 footer-links">
            <h4>Explore</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="{{ route('home') }}">Home</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="{{ route('home') }}#capabilities">Capabilities</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="{{ route('contract_vehicles') }}">Contract Vehicles</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="{{ route('careers') }}">Careers</a></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 footer-links">
            <h4>Ecosystem</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="https://scalepluspro.com" target="_blank" rel="noopener">ScalePlusPro <i class="bi bi-box-arrow-up-right"></i></a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="https://advancementor.academy" target="_blank" rel="noopener">AdvanceMentor Academy <i class="bi bi-box-arrow-up-right"></i></a></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 footer-links">
            <h4>Legal</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="{{ route('privacy') }}">Privacy Policy</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="{{ route('terms') }}">Terms of Service</a></li>
            </ul>
          </div>

        </div>
      </div>
    </div>

    <div class="container">
      <div class="copyright">
        &copy; {{ date('Y') }} <strong><span>Global Scalable Technologies</span></strong>. All Rights Reserved
      </div>
    </div>
  </footer><!-- End Footer -->
