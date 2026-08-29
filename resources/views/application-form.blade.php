@extends('main_layout')

@php
    $deadline = \Carbon\Carbon::parse($job->application_deadline)->endOfDay();
    $locationLabel = $job->location
        ? ($job->location->is_remote ? 'Remote' : $job->location->city . ', ' . $job->location->country)
        : 'Location flexible';
@endphp

@section('title', 'Apply — ' . $job->title . ' — Global Scalable Technologies')

@push('head')
<meta name="robots" content="noindex">
@endpush

@section('main_content')

<!-- ======= Application Hero ======= -->
<section class="application-hero">
    <div class="container" data-aos="fade-up">
        <div class="careers-back-row">
            <a href="{{ route('careers.show', $job->slug) }}" class="careers-back-link">
                <i class="bi bi-arrow-left"></i> Back to role
            </a>
        </div>

        <h1 class="job-detail-title">Apply for {{ $job->title }}</h1>
    </div>
</section>

<!-- ======= Application Form ======= -->
<section class="application-band">
    <div class="container" data-aos="fade-up">
        <div class="row">
            <div class="col-lg-8">
                @if($errors->any())
                    <div class="application-error-summary" role="alert">
                        <p>Please correct the highlighted fields below and submit again.</p>
                    </div>
                @endif

                <form action="{{ route('careers.apply.store', $job->slug) }}" method="POST"
                      enctype="multipart/form-data" class="application-form gst-card-base" id="application-form">
                    @csrf

                    <!-- Personal information -->
                    <div class="application-section">
                        <h2>Personal information</h2>
                        <div class="application-grid">
                            <div>
                                <label for="first_name">First name <span class="required-mark">*</span></label>
                                <input type="text" id="first_name" name="first_name"
                                       value="{{ old('first_name') }}" required
                                       @class(['is-invalid' => $errors->has('first_name')])>
                                @error('first_name') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="last_name">Last name <span class="required-mark">*</span></label>
                                <input type="text" id="last_name" name="last_name"
                                       value="{{ old('last_name') }}" required
                                       @class(['is-invalid' => $errors->has('last_name')])>
                                @error('last_name') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="email">Email address <span class="required-mark">*</span></label>
                                <input type="email" id="email" name="email"
                                       value="{{ old('email') }}" required
                                       @class(['is-invalid' => $errors->has('email')])>
                                @error('email') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="phone">Phone number <span class="required-mark">*</span></label>
                                <input type="tel" id="phone" name="phone"
                                       value="{{ old('phone') }}" required
                                       @class(['is-invalid' => $errors->has('phone')])>
                                @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Professional information -->
                    <div class="application-section">
                        <h2>Professional information</h2>
                        <div class="application-grid">
                            <div>
                                <label for="current_company">Current company</label>
                                <input type="text" id="current_company" name="current_company"
                                       value="{{ old('current_company') }}"
                                       @class(['is-invalid' => $errors->has('current_company')])>
                                @error('current_company') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="current_position">Current position</label>
                                <input type="text" id="current_position" name="current_position"
                                       value="{{ old('current_position') }}"
                                       @class(['is-invalid' => $errors->has('current_position')])>
                                @error('current_position') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="years_of_experience">
                                    Years of experience <span class="required-mark">*</span>
                                </label>
                                <input type="number" id="years_of_experience" name="years_of_experience"
                                       min="0" value="{{ old('years_of_experience') }}" required
                                       @class(['is-invalid' => $errors->has('years_of_experience')])>
                                @error('years_of_experience') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="expected_salary">
                                    Expected salary ({{ $job->salary_currency ?? 'USD' }})
                                </label>
                                <input type="number" id="expected_salary" name="expected_salary"
                                       step="0.01" min="0" value="{{ old('expected_salary') }}"
                                       @class(['is-invalid' => $errors->has('expected_salary')])>
                                <p class="field-hint">Optional. Leave blank if you'd rather discuss it later.</p>
                                @error('expected_salary') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Education -->
                    <div class="application-section">
                        <h2>Education</h2>
                        <div class="application-grid">
                            <div>
                                <label for="highest_degree">Highest degree <span class="required-mark">*</span></label>
                                <select id="highest_degree" name="highest_degree" required
                                        @class(['is-invalid' => $errors->has('highest_degree')])>
                                    <option value="">Select a degree</option>
                                    @foreach(["High School", "Associate's", "Bachelor's", "Master's", "Doctorate", "Other"] as $degree)
                                        <option value="{{ $degree }}" @selected(old('highest_degree') === $degree)>
                                            {{ $degree }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('highest_degree') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="education">Institution name <span class="required-mark">*</span></label>
                                <input type="text" id="education" name="education"
                                       value="{{ old('education') }}" required
                                       @class(['is-invalid' => $errors->has('education')])>
                                @error('education') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Skills -->
                    <div class="application-section">
                        <h2>Skills</h2>
                        <div class="application-grid application-grid--single">
                            <div>
                                <label for="skills">Key skills</label>
                                <textarea id="skills" name="skills" rows="3"
                                          placeholder="Separate with commas"
                                          @class(['is-invalid' => $errors->has('skills')])>{{ old('skills') }}</textarea>
                                @error('skills') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Online profiles -->
                    <div class="application-section">
                        <h2>Online profiles</h2>
                        <div class="application-grid">
                            <div>
                                <label for="linkedin_url">LinkedIn</label>
                                <input type="url" id="linkedin_url" name="linkedin_url"
                                       placeholder="https://linkedin.com/in/yourname"
                                       value="{{ old('linkedin_url') }}"
                                       @class(['is-invalid' => $errors->has('linkedin_url')])>
                                @error('linkedin_url') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="github_url">GitHub</label>
                                <input type="url" id="github_url" name="github_url"
                                       placeholder="https://github.com/yourname"
                                       value="{{ old('github_url') }}"
                                       @class(['is-invalid' => $errors->has('github_url')])>
                                @error('github_url') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="application-grid application-grid--single" style="margin-top: 18px;">
                            <div>
                                <label for="portfolio_url">Portfolio or website</label>
                                <input type="url" id="portfolio_url" name="portfolio_url"
                                       placeholder="https://yourportfolio.com"
                                       value="{{ old('portfolio_url') }}"
                                       @class(['is-invalid' => $errors->has('portfolio_url')])>
                                @error('portfolio_url') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Cover letter -->
                    <div class="application-section">
                        <h2>Cover letter</h2>
                        <div class="application-grid application-grid--single">
                            <div>
                                <label for="cover_letter">Why this role?</label>
                                <textarea id="cover_letter" name="cover_letter" rows="6"
                                          placeholder="What draws you to this position, and what would you bring to it?"
                                          @class(['is-invalid' => $errors->has('cover_letter')])>{{ old('cover_letter') }}</textarea>
                                @error('cover_letter') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Resume -->
                    <div class="application-section">
                        <h2>Resume</h2>
                        <div class="application-grid application-grid--single">
                            <div>
                                <label for="resume_path">Upload your resume <span class="required-mark">*</span></label>
                                <input type="file" id="resume_path" name="resume_path"
                                       accept=".pdf,.doc,.docx" required
                                       @class(['is-invalid' => $errors->has('resume_path')])>
                                <p class="field-hint">PDF, DOC or DOCX. Maximum 5MB.</p>
                                @error('resume_path') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Additional information -->
                    <div class="application-section">
                        <h2>Anything else</h2>
                        <div class="application-grid application-grid--single">
                            <div>
                                <label for="additional_information">Is there anything else we should know?</label>
                                <textarea id="additional_information" name="additional_information" rows="4"
                                          @class(['is-invalid' => $errors->has('additional_information')])>{{ old('additional_information') }}</textarea>
                                @error('additional_information') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="referral_source">How did you hear about this role?</label>
                                <select id="referral_source" name="referral_source"
                                        @class(['is-invalid' => $errors->has('referral_source')])>
                                    <option value="">Select an option</option>
                                    @foreach(['Company Website', 'LinkedIn', 'Indeed', 'Glassdoor', 'Employee Referral', 'Job Fair', 'Other'] as $source)
                                        <option value="{{ $source }}" @selected(old('referral_source') === $source)>
                                            {{ $source }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('referral_source') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="application-section">
                        <div class="application-terms">
                            <input type="checkbox" id="terms_agree" name="terms_agree" value="1"
                                   @checked(old('terms_agree')) required>
                            <label for="terms_agree">
                                I certify that the information in this application is true and complete to the best
                                of my knowledge, and understand that any false statement or omission may disqualify
                                me from consideration.
                            </label>
                        </div>
                        @error('terms_agree') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="application-submit">
                        <button type="submit" class="btn-gst-primary">
                            <i class="bi bi-send"></i> Submit application
                        </button>
                    </div>
                </form>
            </div>

            <!-- Aside -->
            <div class="col-lg-4">
                <div class="application-aside">
                    <div class="application-aside-card gst-card-base">
                        <h2>{{ $job->title }}</h2>
                        <ul class="job-facts" style="margin-top: 0; padding-top: 0; border-top: none;">
                            <li>
                                <span class="job-fact-label">Type</span>
                                <span class="job-fact-value">{{ $job->employment_type }}</span>
                            </li>
                            <li>
                                <span class="job-fact-label">Location</span>
                                <span class="job-fact-value">{{ $locationLabel }}</span>
                            </li>
                            @if($job->salary_min || $job->salary_max)
                                <li>
                                    <span class="job-fact-label">Salary</span>
                                    <span class="job-fact-value">{{ $job->salary_range }}</span>
                                </li>
                            @endif
                            <li>
                                <span class="job-fact-label">Closes</span>
                                <span class="job-fact-value">{{ $deadline->format('M j, Y') }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="application-aside-card gst-card-base">
                        <h2>What happens next</h2>
                        <ol class="application-next-steps">
                            <li>You'll get a confirmation email with your reference number.</li>
                            <li>Our recruiting team reviews your application against the role.</li>
                            <li>If there's a fit, we'll reach out to arrange a first conversation.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><!-- End Application Form -->

@endsection
