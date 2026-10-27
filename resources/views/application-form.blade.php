@extends('main_layout')

@php
    use Illuminate\Support\Facades\Crypt;

    $deadline = \Carbon\Carbon::parse($job->application_deadline)->endOfDay();
    $locationLabel = $job->location_label;

    // Signed-in applicants get their known details prefilled; old() still wins
    // so a failed submission never discards what they typed.
    $user = auth()->user();
    $nameParts = $user ? preg_split('/\s+/', trim($user->name), 2) : [];
    $prefillFirst = $nameParts[0] ?? '';
    $prefillLast = $nameParts[1] ?? '';
    $prefillEmail = $user->email ?? '';

    // Each step lists its fields so a failed submission can flag the steps that need attention.
    $steps = [
        1 => ['title' => 'About you', 'fields' => ['first_name', 'last_name', 'email', 'phone', 'current_location', 'referral_source']],
        2 => ['title' => 'Experience & education', 'fields' => ['current_company', 'current_position', 'years_of_experience', 'expected_salary', 'reason_for_leaving', 'highest_degree', 'education']],
        3 => ['title' => 'Core competencies', 'fields' => ['skills', 'linkedin_url', 'github_url', 'portfolio_url', 'screener']],
        4 => ['title' => 'Documents', 'fields' => ['resume_path', 'cover_letter', 'cover_letter_file', 'attachments']],
        5 => ['title' => 'Availability & disclosures', 'fields' => ['availability', 'availability_other', 'work_authorization', 'willing_to_relocate', 'disability_status', 'additional_information', 'terms_agree']],
    ];
    $stepHasErrors = fn (int $n) => collect($steps[$n]['fields'])->contains(fn ($field) => $errors->has($field) || $errors->has($field . '.*'));
    $firstErrorStep = collect(array_keys($steps))->first(fn ($n) => $stepHasErrors($n)) ?? 1;
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
                        <p>Please correct the highlighted fields and submit again. Steps marked in red need attention.</p>
                        <p>For your security, browsers don't keep chosen files after a failed submission, so please attach your resume and any documents again.</p>
                    </div>
                @endif

                @guest
                    <p class="application-signin-hint">
                        Applying takes a few minutes and no account is needed.
                        <a href="{{ route('login') }}">Already have one? Sign in</a> to fill this in faster.
                    </p>
                @endguest

                <form action="{{ route('careers.apply.store', $job->slug) }}" method="POST"
                      enctype="multipart/form-data" class="application-form gst-card-base" id="application-form"
                      data-initial-step="{{ $firstErrorStep }}">
                    @csrf

                    {{-- Bot defenses. The honeypot is moved off-screen rather than
                         display:none, which some bots detect and skip, and is hidden
                         from assistive tech. The timestamp is encrypted so it cannot
                         be back-dated by the client. --}}
                    <div class="hp-field" aria-hidden="true">
                        <label for="website">Leave this field blank</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <input type="hidden" name="_ts" value="{{ Crypt::encryptString(time()) }}">

                    {{-- Shown by the script; without it every step is simply listed in order. --}}
                    <nav class="application-stepper" aria-label="Application steps" data-js-only hidden>
                        <ol>
                            @foreach($steps as $n => $step)
                                <li>
                                    <button type="button" data-step-goto="{{ $n }}" aria-controls="application-step-{{ $n }}"
                                            @class(['application-stepper-item', 'has-error' => $stepHasErrors($n)])>
                                        <span class="application-stepper-circle" aria-hidden="true">
                                            <span class="application-stepper-number">{{ $n }}</span>
                                            <i class="bi bi-check-lg"></i>
                                        </span>
                                        <span class="application-stepper-label">{{ $step['title'] }}</span>
                                        <span class="visually-hidden" data-step-status></span>
                                    </button>
                                </li>
                            @endforeach
                        </ol>
                    </nav>

                    <!-- Step 1: About you -->
                    <section class="application-step" id="application-step-1" data-step="1" aria-labelledby="application-step-1-title"
                             @if($stepHasErrors(1)) data-has-errors @endif>
                        <header class="application-step-header">
                            <p class="application-step-count">Step 1 of 5</p>
                            <h2 class="application-step-title" id="application-step-1-title" tabindex="-1">About you</h2>
                        </header>

                        <div class="application-section">
                            <div class="application-grid">
                                <div>
                                    <label for="first_name">First name <span class="required-mark">*</span></label>
                                    <input type="text" id="first_name" name="first_name"
                                           value="{{ old('first_name', $prefillFirst) }}" required
                                           autocomplete="given-name"
                                           @class(['is-invalid' => $errors->has('first_name')])>
                                    @error('first_name') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="last_name">Last name <span class="required-mark">*</span></label>
                                    <input type="text" id="last_name" name="last_name"
                                           value="{{ old('last_name', $prefillLast) }}" required
                                           autocomplete="family-name"
                                           @class(['is-invalid' => $errors->has('last_name')])>
                                    @error('last_name') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="email">Email address <span class="required-mark">*</span></label>
                                    <input type="email" id="email" name="email"
                                           value="{{ old('email', $prefillEmail) }}" required
                                           autocomplete="email"
                                           @class(['is-invalid' => $errors->has('email')])>
                                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="phone">Phone number <span class="required-mark">*</span></label>
                                    <input type="tel" id="phone" name="phone"
                                           value="{{ old('phone') }}" required autocomplete="tel"
                                           @class(['is-invalid' => $errors->has('phone')])>
                                    @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="current_location">Current location <span class="required-mark">*</span></label>
                                    <input type="text" id="current_location" name="current_location"
                                           value="{{ old('current_location') }}" required
                                           placeholder="City, State" autocomplete="address-level2"
                                           @class(['is-invalid' => $errors->has('current_location')])>
                                    @error('current_location') <p class="field-error">{{ $message }}</p> @enderror
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

                        @include('partials.application-step-nav', ['n' => 1, 'steps' => $steps])
                    </section>

                    <!-- Step 2: Experience & education -->
                    <section class="application-step" id="application-step-2" data-step="2" aria-labelledby="application-step-2-title"
                             @if($stepHasErrors(2)) data-has-errors @endif>
                        <header class="application-step-header">
                            <p class="application-step-count">Step 2 of 5</p>
                            <h2 class="application-step-title" id="application-step-2-title" tabindex="-1">Experience &amp; education</h2>
                        </header>

                        <div class="application-section">
                            <h3>Current or most recent role</h3>
                            <div class="application-grid">
                                <div>
                                    <label for="current_company">Company</label>
                                    <input type="text" id="current_company" name="current_company"
                                           value="{{ old('current_company') }}" autocomplete="organization"
                                           @class(['is-invalid' => $errors->has('current_company')])>
                                    @error('current_company') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="current_position">Position</label>
                                    <input type="text" id="current_position" name="current_position"
                                           value="{{ old('current_position') }}" autocomplete="organization-title"
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
                            <div class="application-grid application-grid--single" style="margin-top: 18px;">
                                <div>
                                    <label for="reason_for_leaving">Reason for leaving your previous job</label>
                                    <textarea id="reason_for_leaving" name="reason_for_leaving" rows="3" maxlength="2000"
                                              placeholder="A sentence or two is plenty."
                                              @class(['is-invalid' => $errors->has('reason_for_leaving')])>{{ old('reason_for_leaving') }}</textarea>
                                    @error('reason_for_leaving') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="application-section">
                            <h3>Education</h3>
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

                        @include('partials.application-step-nav', ['n' => 2, 'steps' => $steps])
                    </section>

                    <!-- Step 3: Core competencies -->
                    <section class="application-step" id="application-step-3" data-step="3" aria-labelledby="application-step-3-title"
                             @if($stepHasErrors(3)) data-has-errors @endif>
                        <header class="application-step-header">
                            <p class="application-step-count">Step 3 of 5</p>
                            <h2 class="application-step-title" id="application-step-3-title" tabindex="-1">Core competencies</h2>
                        </header>

                        @if($job->screenerQuestions->isNotEmpty())
                            <div class="application-section">
                                <h3>Screening questions</h3>
                                @foreach($job->screenerQuestions as $question)
                                    <fieldset class="application-fieldset application-fieldset--question">
                                        <legend>{{ $question->question }} <span class="required-mark">*</span></legend>
                                        <div class="application-choices">
                                            <label><input type="radio" name="screener[{{ $question->id }}]" value="1" required @checked(old('screener.' . $question->id) === '1')> Yes</label>
                                            <label><input type="radio" name="screener[{{ $question->id }}]" value="0" @checked(old('screener.' . $question->id) === '0')> No</label>
                                        </div>
                                        @error('screener.' . $question->id) <p class="field-error">{{ $message }}</p> @enderror
                                    </fieldset>
                                @endforeach
                            </div>
                        @endif

                        <div class="application-section">
                            <h3>Skills</h3>
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

                        <div class="application-section">
                            <h3>Online profiles</h3>
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

                        @include('partials.application-step-nav', ['n' => 3, 'steps' => $steps])
                    </section>

                    <!-- Step 4: Documents -->
                    <section class="application-step" id="application-step-4" data-step="4" aria-labelledby="application-step-4-title"
                             @if($stepHasErrors(4)) data-has-errors @endif>
                        <header class="application-step-header">
                            <p class="application-step-count">Step 4 of 5</p>
                            <h2 class="application-step-title" id="application-step-4-title" tabindex="-1">Documents</h2>
                        </header>

                        <div class="application-section">
                            <h3>Resume and supporting documents</h3>
                            <div class="application-grid application-grid--single">
                                <div>
                                    <label for="resume_path">Upload your resume <span class="required-mark">*</span></label>
                                    <input type="file" id="resume_path" name="resume_path"
                                           accept=".pdf,.doc,.docx" required
                                           @class(['is-invalid' => $errors->has('resume_path')])>
                                    <p class="field-hint">PDF, DOC or DOCX. Maximum 5MB.</p>
                                    @error('resume_path') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="attachments">Certifications and other supporting documents</label>
                                    <input type="file" id="attachments" name="attachments[]" multiple
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                           @class(['is-invalid' => $errors->has('attachments') || $errors->has('attachments.*')])>
                                    <p class="field-hint">Optional. Up to {{ \App\Http\Requests\StoreJobApplicationRequest::MAX_ATTACHMENTS }} files: PDF, DOC, DOCX, JPG or PNG, 5MB each.</p>
                                    @error('attachments') <p class="field-error">{{ $message }}</p> @enderror
                                    @if($errors->has('attachments.*'))
                                        <p class="field-error">{{ $errors->first('attachments.*') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="application-section">
                            <h3>Cover letter</h3>
                            <p class="field-hint" style="margin: -6px 0 14px;">Optional. Type it below, upload a file, or both.</p>
                            <div class="application-grid application-grid--single">
                                <div>
                                    <label for="cover_letter">Why this role?</label>
                                    <textarea id="cover_letter" name="cover_letter" rows="6" maxlength="10000"
                                              placeholder="What draws you to this position, and what would you bring to it?"
                                              @class(['is-invalid' => $errors->has('cover_letter')])>{{ old('cover_letter') }}</textarea>
                                    @error('cover_letter') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="cover_letter_file">Or upload a cover letter</label>
                                    <input type="file" id="cover_letter_file" name="cover_letter_file"
                                           accept=".pdf,.doc,.docx"
                                           @class(['is-invalid' => $errors->has('cover_letter_file')])>
                                    <p class="field-hint">PDF, DOC or DOCX. Maximum 5MB.</p>
                                    @error('cover_letter_file') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        @include('partials.application-step-nav', ['n' => 4, 'steps' => $steps])
                    </section>

                    <!-- Step 5: Availability & disclosures -->
                    <section class="application-step" id="application-step-5" data-step="5" aria-labelledby="application-step-5-title"
                             @if($stepHasErrors(5)) data-has-errors @endif>
                        <header class="application-step-header">
                            <p class="application-step-count">Step 5 of 5</p>
                            <h2 class="application-step-title" id="application-step-5-title" tabindex="-1">Availability &amp; disclosures</h2>
                        </header>

                        <div class="application-section">
                            <h3>Availability and eligibility</h3>
                            <div class="application-grid">
                                <div>
                                    <label for="availability">Availability to start <span class="required-mark">*</span></label>
                                    <select id="availability" name="availability" required
                                            @class(['is-invalid' => $errors->has('availability')])>
                                        <option value="">Select an option</option>
                                        @foreach(\App\Models\JobApplication::AVAILABILITY as $value => $label)
                                            <option value="{{ $value }}" @selected(old('availability') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('availability') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div id="availability_other_field" @if(old('availability') !== 'other') hidden @endif>
                                    <label for="availability_other">When could you start? <span class="required-mark">*</span></label>
                                    <input type="text" id="availability_other" name="availability_other"
                                           value="{{ old('availability_other') }}" maxlength="191"
                                           placeholder="e.g. After my contract ends in March"
                                           @class(['is-invalid' => $errors->has('availability_other')])>
                                    @error('availability_other') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="work_authorization">Work authorization <span class="required-mark">*</span></label>
                                    <select id="work_authorization" name="work_authorization" required
                                            @class(['is-invalid' => $errors->has('work_authorization')])>
                                        <option value="">Select an option</option>
                                        @foreach(\App\Models\JobApplication::WORK_AUTHORIZATION as $value => $label)
                                            <option value="{{ $value }}" @selected(old('work_authorization') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('work_authorization') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                                <fieldset class="application-fieldset">
                                    <legend>Willing to relocate? <span class="required-mark">*</span></legend>
                                    <div class="application-choices">
                                        <label><input type="radio" name="willing_to_relocate" value="1" required @checked(old('willing_to_relocate') === '1')> Yes</label>
                                        <label><input type="radio" name="willing_to_relocate" value="0" @checked(old('willing_to_relocate') === '0')> No</label>
                                    </div>
                                    @error('willing_to_relocate') <p class="field-error">{{ $message }}</p> @enderror
                                </fieldset>
                            </div>
                        </div>

                        <div class="application-section">
                            <h3>Voluntary self-identification of disability</h3>
                            <p class="field-hint" style="margin: -6px 0 14px;">
                                Answering is voluntary and will not affect your application. Your answer is stored
                                separately and is not shown to the people reviewing your application.
                            </p>
                            <fieldset class="application-fieldset">
                                <legend>Do you have a disability?</legend>
                                <div class="application-choices application-choices--stacked">
                                    @foreach(\App\Models\DisabilitySelfIdentification::OPTIONS as $value => $label)
                                        <label>
                                            <input type="radio" name="disability_status" value="{{ $value }}" @checked(old('disability_status') === $value)>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                @error('disability_status') <p class="field-error">{{ $message }}</p> @enderror
                            </fieldset>
                        </div>

                        <div class="application-section">
                            <h3>Anything else</h3>
                            <div class="application-grid application-grid--single">
                                <div>
                                    <label for="additional_information">Is there anything else we should know?</label>
                                    <textarea id="additional_information" name="additional_information" rows="4"
                                              @class(['is-invalid' => $errors->has('additional_information')])>{{ old('additional_information') }}</textarea>
                                    @error('additional_information') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

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
                            <button type="button" class="btn-gst-outline" data-step-prev data-js-only hidden>
                                <i class="bi bi-arrow-left"></i> Previous
                            </button>
                            <button type="submit" class="btn-gst-primary">
                                <i class="bi bi-send"></i> Submit application
                            </button>
                        </div>
                    </section>
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
                            <li>
                                <span class="job-fact-label">Work arrangement</span>
                                <span class="job-fact-value">{{ $job->work_arrangement_label }}</span>
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

<script src="{{ \App\Support\VersionedAsset::url('assets/js/application-form.js') }}"></script>

@endsection
