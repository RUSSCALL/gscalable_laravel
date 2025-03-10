@extends('main_layout')

@section('main_content')
 <!-- ======= Careers Section ======= -->
<section id="careers" class="careers">
    <div class="container" data-aos="fade-up">
      <!-- Horizontal Line -->
      <div class="row">
        <div class="col-12">
          <hr class="careers-divider">
        </div>
      </div>
  
      <div class="row">
        <div class="col-lg-6">
          <h1 class="careers-title">CAREERS</h1>
          
          <div class="careers-intro">
            <p>
              <a href="{{ route('login') }}" class="careers-back-link"><i class="bi bi-arrow-left"></i> Returning Candidate? <span class="log-back-in">Log back in</span></a>
            </p>
            
            <p class="careers-description">
              Global Scalable Technologies (GST)is a dynamic, forward-thinking company always looking to expand our staff of skilled and experienced professionals. Global Scalable Technologies (GST) has made a name for itself as the employer of choice due to its commitment to retain quality talent by investing in our employees and showing them we care about their happiness and well-being. Our commitment to excellence and strong corporate culture allows us to attract and retain the best and brightest cybersecurity professionals. We look forward to reviewing your resume for an opportunity to join our team!
            </p>
          </div>
  
          <!-- START JOB SEARCH FORM-->
          <div class="job-search-container">
            <p>Start your job search here</p>
            <form action="{{ route('jobapplicant.search') }}" method="GET" class="search-form">
              @csrf
              <div class="input-group mb-3">
                <input type="text" class="form-control" name="search" placeholder="Enter search criteria" aria-label="Search" value="{{ request('search') }}">
                <button class="btn btn-search" type="submit">Search</button>
              </div>
            
              <div class="search-filters">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="location" class="form-label">Location</label>
                      <select class="form-select" id="location" name="location">
                        <option value="">Any</option>
                        @if(isset($locations) && count($locations) > 0)
                          @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ request('location') == $location->id ? 'selected' : '' }}>
                              {{ $location->city }}, {{ $location->country }}
                              @if($location->is_remote) (Remote) @endif
                            </option>
                          @endforeach
                        @endif
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3 text-end">
                      <label for="sort" class="form-label">Sort By</label>
                      <select class="form-select" id="sort" name="sort">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Date - Newest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Date - Oldest First</option>
                        <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>Featured Jobs</option>
                        <option value="deadline" {{ request('sort') == 'deadline' ? 'selected' : '' }}>Deadline - Soonest First</option>
                        <option value="salary" {{ request('sort') == 'salary' ? 'selected' : '' }}>Salary - Highest First</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="category" class="form-label">Category</label>
                      <select class="form-select" id="category" name="category">
                        <option value="">Any</option>
                        @if(isset($categories) && count($categories) > 0)
                          @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                              {{ $category->name }}
                            </option>
                          @endforeach
                        @endif
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="employment_type" class="form-label">Employment Type</label>
                      <select class="form-select" id="employment_type" name="employment_type">
                        <option value="">Any</option>
                        @if(isset($employmentTypes) && count($employmentTypes) > 0)
                          @foreach($employmentTypes as $type)
                            <option value="{{ $type }}" {{ request('employment_type') == $type ? 'selected' : '' }}>
                              {{ $type }}
                            </option>
                          @endforeach
                        @endif
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
  
          <!-- Job Listings -->
          <div class="job-listings-container">
            @if(isset($jobPostings))
              <h6 class="search-results-heading">
                Search Results Page {{ $jobPostings->currentPage() }} of {{ $jobPostings->lastPage() }}
                ({{ $jobPostings->total() }} jobs found)
              </h6>
              
              @forelse ($jobPostings as $job)
                <div class="job-listing">
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
                  <h4 class="job-title">
                    <a href="{{ route('jobapplicant.show', $job->slug) }}">{{ $job->title }}</a>
                    @if($job->is_featured)
                      <span class="badge bg-warning text-dark">Featured</span>
                    @endif
                  </h4>
                  <p class="job-description">
                    {{ Str::limit(strip_tags($job->description), 200) }}
                  </p>
                  <div class="job-footer">
                    <span class="job-type me-3">{{ $job->employment_type }}</span>
                    <span class="job-level me-3">{{ $job->experience_level }}</span>
                    
                    @php
                      $deadline = \Carbon\Carbon::parse($job->application_deadline);
                      $daysLeft = $deadline->diffInDays(now());
                    @endphp
                    
                    @if($deadline->isPast())
                      <span class="badge bg-danger">Expired</span>
                    @elseif($daysLeft <= 5)
                      <span class="badge bg-warning text-dark">{{ $daysLeft }} days left</span>
                    @else
                      <span class="deadline">Apply by {{ $deadline->format('M d, Y') }}</span>
                    @endif
                  </div>
                </div>
              @empty
                <div class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>
                  No job listings found matching your criteria. Try adjusting your search.
                </div>
              @endforelse
              
              <!-- Pagination -->
              <div class="d-flex justify-content-center mt-4">
                {{ $jobPostings->links() }}
              </div>
            @else
              <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Please use the search form above to find job opportunities.
              </div>
            @endif
          </div>
        </div>
        
        <!-- Right Column -->
        <div class="col-lg-6">
          <div class="connect-section">
            <h2 class="connect-title">CONNECT WITH US!</h2>
            
            <div class="team-member">
              <img src="{{ asset('assets/img/team/catherine.jpg') }}" alt="Catherine Buchanan" class="img-fluid team-image">
              <div class="team-info">
                <h3 class="member-name">CATHERINE BUCHANAN</h3>
                <h4 class="member-title">EVP, TALENT & CULTURE STRATEGY</h4>
                <a href="https://www.linkedin.com/in/catherine-buchanan" class="linkedin"><i class="bi bi-linkedin"></i></a>
              </div>
            </div>
            
            <div class="team-member">
              <img src="{{ asset('assets/img/team/recruiter.jpg') }}" alt="Recruiter" class="img-fluid team-image">
              <div class="team-info">
                <a href="https://www.linkedin.com/company/Global Scalable Technologies (GST)-solutions" class="linkedin"><i class="bi bi-linkedin"></i></a>
              </div>
            </div>

            <!-- Job Statistics -->
            @if(isset($stats))
              <div class="job-stats mt-4">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title">Job Statistics</h5>
                    <ul class="list-group list-group-flush">
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total Open Positions
                        <span class="badge bg-primary rounded-pill">{{ $stats['total_jobs'] }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        New in Last Week
                        <span class="badge bg-success rounded-pill">{{ $stats['recent_jobs'] }}</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            @endif

            <!-- Featured Jobs -->
            @if(isset($featuredJobs) && $featuredJobs->count() > 0)
              <div class="featured-jobs mt-4">
                <div class="card">
                  <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Featured Opportunities</h5>
                  </div>
                  <div class="card-body">
                    <ul class="list-group list-group-flush">
                      @foreach($featuredJobs as $featuredJob)
                        <li class="list-group-item">
                          <a href="{{ route('jobapplicant.show', $featuredJob->slug) }}" class="featured-job-link">
                            {{ $featuredJob->title }}
                          </a>
                          <div class="small text-muted">
                            {{ $featuredJob->location ? $featuredJob->location->city . ', ' . $featuredJob->location->country : 'Remote' }}
                          </div>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section><!-- End Careers Section -->
  
  <!-- CSS Styles for Careers Section -->
  <style>
  .careers-divider {
    border-top: 1px solid #f7941d;
    margin: 40px 0;
  }
  
  .careers-title {
    color: #f7941d;
    font-size: 50px;
    font-weight: 700;
    margin-bottom: 30px;
  }
  
  .careers-back-link {
    color: #333;
    text-decoration: none;
  }
  
  .careers-back-link:hover {
    text-decoration: underline;
  }
  
  .log-back-in {
    color: #f7941d;
    font-weight: 600;
  }
  
  .careers-description {
    margin-bottom: 30px;
    line-height: 1.6;
  }
  
  .job-search-container {
    margin-bottom: 30px;
  }
  
  .btn-search {
    background-color: #f7941d;
    color: white;
    border: none;
  }
  
  .btn-search:hover {
    background-color: #e58305;
    color: white;
  }
  
  .search-results-heading {
    margin: 20px 0;
    font-weight: 600;
  }
  
  .job-listing {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
  }
  
  .job-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 14px;
  }
  
  .job-id {
    color: #666;
  }
  
  .job-title {
    margin-bottom: 10px;
  }
  
  .job-title a {
    color: #f7941d;
    text-decoration: none;
  }
  
  .job-title a:hover {
    text-decoration: underline;
  }
  
  .job-description {
    margin-bottom: 10px;
    font-size: 14px;
    line-height: 1.5;
  }

  .job-footer {
    font-size: 13px;
    color: #666;
  }

  .job-type, .job-level {
    display: inline-block;
  }

  .deadline {
    font-style: italic;
  }
  
  .connect-section {
    padding: 30px;
  }
  
  .connect-title {
    color: #f7941d;
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 30px;
  }
  
  .team-member {
    margin-bottom: 30px;
    position: relative;
  }
  
  .team-image {
    width: 100%;
    border-radius: 5px;
  }
  
  .team-info {
    margin-top: 15px;
    text-align: center;
  }
  
  .member-name {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
  }
  
  .member-title {
    font-size: 18px;
    font-weight: 600;
    color: #f7941d;
    margin-bottom: 10px;
  }
  
  .linkedin {
    font-size: 24px;
    color: #0077b5;
  }

  .featured-job-link {
    color: #f7941d;
    text-decoration: none;
    font-weight: 600;
  }

  .featured-job-link:hover {
    text-decoration: underline;
  }
  
  @media (max-width: 991px) {
    .connect-section {
      margin-top: 50px;
    }
  }
  </style>   
@endsection