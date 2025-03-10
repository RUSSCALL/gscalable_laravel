@extends('main_layout')

@section('main_content')
<!-- ======= Job Application Section ======= -->
<section id="job-application" class="job-application">
  <div class="container" data-aos="fade-up">
    <!-- Horizontal Line -->
    <div class="row">
      <div class="col-12">
        <hr class="careers-divider">
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <!-- Back Button -->
        <div class="mb-4">
          <a href="{{ route('jobapplicant.show', $job->slug) }}" class="careers-back-link">
            <i class="bi bi-arrow-left"></i> Back to job details
          </a>
        </div>

        <!-- Application Header -->
        <div class="job-detail-header mb-4">
          <h1 class="job-detail-title">Apply for: {{ $job->title }}</h1>
          <div class="job-header">
            <div class="job-locations">
              @if($job->location)
                @if($job->location->is_remote)
                  US-Remote / Telework
                @else
                  {{ $job->location->city }}, {{ $job->location->country }}
                @endif
              @else
                Location Not Specified
              @endif
            </div>
            <div class="job-id">{{ date('Y') }}-{{ $job->id }}</div>
          </div>
        </div>

        <!-- Application Form -->
        <div class="application-form-container">
          <form action="{{ route('jobapplication.store') }}" method="POST" enctype="multipart/form-data" class="application-form">
            @csrf
            <input type="hidden" name="job_posting_id" value="{{ $job->id }}">
            
            <!-- Personal Information Section -->
              <div class="application-section mb-4">
                <h5><i class="bi bi-person"></i> Personal Information</h5>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                  </div>
                </div>
              </div>

              <!-- Professional Information Section -->
              <div class="application-section mb-4">
                <h5><i class="bi bi-briefcase"></i> Professional Information</h5>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="current_company" class="form-label">Current Company</label>
                    <input type="text" class="form-control" id="current_company" name="current_company" value="{{ old('current_company') }}">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="current_position" class="form-label">Current Position</label>
                    <input type="text" class="form-control" id="current_position" name="current_position" value="{{ old('current_position') }}">
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="years_of_experience" class="form-label">Years of Experience</label>
                    <input type="number" class="form-control" id="years_of_experience" name="years_of_experience" min="0" value="{{ old('years_of_experience') }}">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="expected_salary" class="form-label">Expected Salary ({{ $job->salary_currency ?? 'USD' }})</label>
                    <input type="number" class="form-control" id="expected_salary" name="expected_salary" step="0.01" value="{{ old('expected_salary') }}">
                  </div>
                </div>
              </div>

              <!-- Education Section -->
              <div class="application-section mb-4">
                <h5><i class="bi bi-mortarboard"></i> Education</h5>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="highest_degree" class="form-label">Highest Degree</label>
                    <select class="form-select" id="highest_degree" name="highest_degree">
                      <option value="" {{ old('highest_degree') == '' ? 'selected' : '' }}>Select a degree</option>
                      <option value="High School" {{ old('highest_degree') == 'High School' ? 'selected' : '' }}>High School</option>
                      <option value="Associate's" {{ old('highest_degree') == "Associate's" ? 'selected' : '' }}>Associate's Degree</option>
                      <option value="Bachelor's" {{ old('highest_degree') == "Bachelor's" ? 'selected' : '' }}>Bachelor's Degree</option>
                      <option value="Master's" {{ old('highest_degree') == "Master's" ? 'selected' : '' }}>Master's Degree</option>
                      <option value="Doctorate" {{ old('highest_degree') == 'Doctorate' ? 'selected' : '' }}>Doctorate</option>
                      <option value="Other" {{ old('highest_degree') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="education" class="form-label">Institution Name</label>
                    <input type="text" class="form-control" id="education" name="education" value="{{ old('education') }}">
                  </div>
                </div>
              </div>

              <!-- Skills & Qualifications Section -->
              <div class="application-section mb-4">
                <h5><i class="bi bi-star"></i> Skills & Qualifications</h5>
                <div class="mb-3">
                  <label for="skills" class="form-label">Key Skills <small class="text-muted">(separate with commas)</small></label>
                  <textarea class="form-control" id="skills" name="skills" rows="3">{{ old('skills') }}</textarea>
                </div>
              </div>

              <!-- Online Profiles Section -->
              <div class="application-section mb-4">
                <h5><i class="bi bi-link-45deg"></i> Online Profiles</h5>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="linkedin_url" class="form-label">LinkedIn Profile</label>
                    <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/in/yourusername" value="{{ old('linkedin_url') }}">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="github_url" class="form-label">GitHub Profile</label>
                    <input type="url" class="form-control" id="github_url" name="github_url" placeholder="https://github.com/yourusername" value="{{ old('github_url') }}">
                  </div>
                </div>
                <div class="mb-3">
                  <label for="portfolio_url" class="form-label">Portfolio/Website</label>
                  <input type="url" class="form-control" id="portfolio_url" name="portfolio_url" placeholder="https://yourportfolio.com" value="{{ old('portfolio_url') }}">
                </div>
              </div>

              <!-- Cover Letter Section -->
              <div class="application-section mb-4">
                <h5><i class="bi bi-file-text"></i> Cover Letter</h5>
                <div class="mb-3">
                  <textarea class="form-control" id="cover_letter" name="cover_letter" rows="6" placeholder="Why are you interested in this position? What makes you a good fit?">{{ old('cover_letter') }}</textarea>
                </div>
              </div>

              <!-- Resume Upload Section -->
              <div class="application-section mb-4">
                <h5><i class="bi bi-file-earmark-pdf"></i> Resume/CV <span class="text-danger">*</span></h5>
                <div class="mb-3">
                  <input type="file" class="form-control" id="resume_path" name="resume_path" accept=".pdf,.doc,.docx" required>
                  <div class="form-text">Accepted formats: PDF, DOC, DOCX. Max size: 5MB</div>
                </div>
              </div>

              <!-- Additional Information Section -->
              <div class="application-section mb-4">
                <h5><i class="bi bi-info-circle"></i> Additional Information</h5>
                <div class="mb-3">
                  <label for="additional_information" class="form-label">Is there anything else you'd like us to know?</label>
                  <textarea class="form-control" id="additional_information" name="additional_information" rows="4">{{ old('additional_information') }}</textarea>
                </div>
                <div class="mb-3">
                  <label for="referral_source" class="form-label">How did you hear about this position?</label>
                  <select class="form-select" id="referral_source" name="referral_source">
                    <option value="" {{ old('referral_source') == '' ? 'selected' : '' }}>Select an option</option>
                    <option value="Company Website" {{ old('referral_source') == 'Company Website' ? 'selected' : '' }}>Company Website</option>
                    <option value="LinkedIn" {{ old('referral_source') == 'LinkedIn' ? 'selected' : '' }}>LinkedIn</option>
                    <option value="Indeed" {{ old('referral_source') == 'Indeed' ? 'selected' : '' }}>Indeed</option>
                    <option value="Glassdoor" {{ old('referral_source') == 'Glassdoor' ? 'selected' : '' }}>Glassdoor</option>
                    <option value="Employee Referral" {{ old('referral_source') == 'Employee Referral' ? 'selected' : '' }}>Employee Referral</option>
                    <option value="Job Fair" {{ old('referral_source') == 'Job Fair' ? 'selected' : '' }}>Job Fair</option>
                    <option value="Other" {{ old('referral_source') == 'Other' ? 'selected' : '' }}>Other</option>
                  </select>
                </div>
              </div>

              <!-- Terms & Conditions Section -->
              <div class="application-section mb-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="terms_agree" name="terms_agree" {{ old('terms_agree') ? 'checked' : '' }} required>
                  <label class="form-check-label" for="terms_agree">
                    I certify that all information provided in this application is true and complete to the best of my knowledge. 
                    I understand that any false information or omission may disqualify me from further consideration for employment.
                  </label>
                </div>
              </div>

            <!-- Submit Button -->
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-apply btn-lg">
                <i class="bi bi-send"></i> Submit Application
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Right Column -->
      <div class="col-lg-4">
        <!-- Job Summary Card -->
        <div class="card job-summary mb-4">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Job Summary</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <h6><i class="bi bi-briefcase me-2"></i>{{ $job->employment_type }}</h6>
            </div>
            <div class="mb-3">
              <h6><i class="bi bi-geo-alt me-2"></i>
                @if($job->location)
                  @if($job->location->is_remote)
                    US-Remote / Telework
                  @else
                    {{ $job->location->city }}, {{ $job->location->country }}
                  @endif
                @else
                  Location Not Specified
                @endif
              </h6>
            </div>
            @if($job->salary_min || $job->salary_max)
              <div class="mb-3">
                <h6><i class="bi bi-currency-dollar me-2"></i>
                  @if($job->salary_min && $job->salary_max)
                    {{ number_format($job->salary_min, 0) }} - {{ number_format($job->salary_max, 0) }} {{ $job->salary_currency }}
                    @if($job->salary_period)
                      ({{ $job->salary_period }})
                    @endif
                  @elseif($job->salary_min)
                    From {{ number_format($job->salary_min, 0) }} {{ $job->salary_currency }}
                    @if($job->salary_period)
                      ({{ $job->salary_period }})
                    @endif
                  @elseif($job->salary_max)
                    Up to {{ number_format($job->salary_max, 0) }} {{ $job->salary_currency }}
                    @if($job->salary_period)
                      ({{ $job->salary_period }})
                    @endif
                  @endif
                </h6>
              </div>
            @endif
            <div class="mb-3">
              <h6><i class="bi bi-calendar-event me-2"></i>Apply by {{ \Carbon\Carbon::parse($job->application_deadline)->format('M d, Y') }}</h6>
            </div>
          </div>
        </div>

        <!-- Application Tips Card -->
        <div class="card application-tips-card mb-4">
          <div class="card-header">
            <h5 class="mb-0">Application Tips</h5>
          </div>
          <div class="card-body">
            <ul class="application-tips-list">
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>Tailor your resume to highlight relevant experience</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>Be specific about your achievements and skills</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>Proofread your application before submitting</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>Include a personalized cover letter</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i>Research the company and position beforehand</li>
            </ul>
          </div>
        </div>

        <!-- Company Info Card -->
        <div class="card company-info">
          <div class="card-body">
            <h5 class="card-title">About Global Scalable Technologies</h5>
            <p>Global Scalable Technologies (GST) is a dynamic, forward-thinking company committed to excellence in cybersecurity solutions.</p>
            <div class="text-center mt-3">
              <a href="#" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-building"></i> Visit Company Page
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section><!-- End Job Application Section -->


@endsection