@extends('main_layout')

@section('main_content')
<!-- ======= Job Posting Section ======= -->
<section id="job-posting" class="job-posting">
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
          <a href="{{ route('careers') }}" class="careers-back-link">
            <i class="bi bi-arrow-left"></i> Back to all jobs
          </a>
        </div>

        <!-- Job Header -->
        <div class="job-detail-header">
          <h1 class="job-detail-title">{{ $job->title }}</h1>
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
          
          <div class="job-meta">
            <span class="job-type me-3"><i class="bi bi-briefcase me-1"></i> {{ $job->employment_type }}</span>
            <span class="job-level me-3"><i class="bi bi-bar-chart me-1"></i> {{ $job->experience_level }}</span>
            @if($job->category)
              <span class="job-category me-3"><i class="bi bi-bookmark me-1"></i> {{ $job->category->name }}</span>
            @endif
            
            @php
              $deadline = \Carbon\Carbon::parse($job->application_deadline);
              $daysLeft = $deadline->diffInDays(now());
            @endphp
            
            @if($deadline->isPast())
              <span class="badge bg-danger">Application Closed</span>
            @elseif($daysLeft <= 5)
              <span class="badge bg-warning text-dark">{{ $daysLeft }} days left to apply</span>
            @else
              <span class="deadline"><i class="bi bi-calendar-event me-1"></i> Apply by {{ $deadline->format('M d, Y') }}</span>
            @endif
          </div>
          
          @if($job->is_featured)
            <div class="featured-badge mt-2">
              <span class="badge bg-warning text-dark">Featured Position</span>
            </div>
          @endif
        </div>

        <!-- Job Details -->
        <div class="job-detail-content mt-4">
          <!-- Salary Information -->
          @if($job->salary_min || $job->salary_max)
            <div class="salary-info mb-4">
              <h5><i class="bi bi-currency-dollar"></i> Compensation</h5>
              <p>
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
              </p>
            </div>
          @endif

          <!-- Job Description -->
          <div class="job-section mb-4">
            <h5><i class="bi bi-info-circle"></i> Job Description</h5>
            <div>
              {!! $job->description !!}
            </div>
          </div>

          <!-- Responsibilities -->
          <div class="job-section mb-4">
            <h5><i class="bi bi-list-task"></i> Responsibilities</h5>
            <div>
              {!! $job->responsibilities !!}
            </div>
          </div>

          <!-- Requirements -->
          <div class="job-section mb-4">
            <h5><i class="bi bi-check-circle"></i> Requirements</h5>
            <div>
              {!! $job->requirements !!}
            </div>
          </div>

          <!-- Benefits -->
          @if($job->benefits)
            <div class="job-section mb-4">
              <h5><i class="bi bi-gift"></i> Benefits</h5>
              <div>
                {!! $job->benefits !!}
              </div>
            </div>
          @endif

          <!-- Apply Section -->
          <div class="apply-section mt-5">
            @if(!$deadline->isPast())
              <div class="d-grid gap-2">
                @auth
                  @if(isset($hasApplied) && $hasApplied)
                    <a href="{{ route('careers') }}" class="btn btn-outline-info btn-lg">
                      <i class="bi bi-arrow-left"></i> You have already applied!
                    </a>
                  @else
                    <a href="{{route('job.apply' , $job->slug)}}" class="btn btn-apply btn-lg">
                      <i class="bi bi-send"></i> Apply Now
                    </a>
                  @endif
                @else
                  <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">
                    <i class="bi bi-person"></i> Login to Apply
                  </a>
                @endauth
                
                <button class="btn btn-share" type="button" onclick="shareJob()">
                  <i class="bi bi-share"></i> Share with a Friend
                </button>
              </div>
            @else
              <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                This position is no longer accepting applications.
              </div>
            @endif
          </div>

          <!-- Job Stats -->
          <div class="job-stats mt-4">
            <div class="d-flex justify-content-center text-muted small">
              <span class="me-3"><i class="bi bi-eye"></i> {{ $job->views_count }} views</span>
              <span class="me-3"><i class="bi bi-people"></i> {{ $job->applications_count }} applications</span>
              <span><i class="bi bi-calendar-check"></i> Posted: {{ \Carbon\Carbon::parse($job->published_at)->format('M d, Y') }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="col-lg-4">
        <!-- Quick Apply Card -->
        <div class="card job-quick-apply mb-4">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Quick Apply</h5>
          </div>
          <div class="card-body">
            @if(!$deadline->isPast())
            <p>Ready to join our team? Apply now in just a few steps.</p>
            
            @auth
            <div class="d-grid">
              @if(isset($hasApplied) && $hasApplied)
                <a href="{{ route('careers') }}" class="btn btn-outline-info">
                  <i class="bi bi-arrow-left"></i> You have already applied!
                </a>
              @else
                <a href="{{ route('job.apply', $job->slug) }}" class="btn btn-apply">
                  <i class="bi bi-send"></i> Apply for this Job
                </a>
              @endif
            </div>
          @else
            <div class="d-grid">
              <a href="{{ route('login') }}" class="btn btn-outline-primary">
                <i class="bi bi-person"></i> Login to Apply
              </a>
            </div>
          @endauth
            
          @else
            <p class="text-danger">
              <i class="bi bi-exclamation-triangle me-2"></i>
              This position is no longer accepting applications.
            </p>
          @endif
          </div>
        </div>

        <!-- Company Info Card -->
        <div class="card company-info mb-4">
          <div class="card-body">
            <h5 class="card-title">About Global Scalable Technologies</h5>
            <p>Global Scalable Technologies (GST) is a dynamic, forward-thinking company committed to excellence in cybersecurity solutions.</p>
          </div>
        </div>

        <!-- Similar Jobs Card -->
        @if(count($relatedJobs) > 0)
          <div class="card similar-jobs">
            <div class="card-header">
              <h5 class="mb-0">Similar Positions</h5>
            </div>
            <div class="card-body p-0">
              <ul class="list-group list-group-flush">
                @foreach($relatedJobs as $relatedJob)
                  <li class="list-group-item">
                    <a href="{{ route('jobapplicant.show', $relatedJob->slug) }}" class="similar-job-link">
                      {{ $relatedJob->title }}
                    </a>
                    <div class="small text-muted">
                      @if($relatedJob->location)
                        @if($relatedJob->location->is_remote)
                          US-Remote
                        @else
                          {{ $relatedJob->location->city }}
                        @endif
                      @else
                        Location Not Specified
                      @endif
                      <span class="mx-1">•</span>
                      {{ $relatedJob->employment_type }}
                    </div>
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</section><!-- End Job Posting Section -->

@endsection