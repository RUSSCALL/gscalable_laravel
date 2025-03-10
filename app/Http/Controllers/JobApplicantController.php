<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\JobCategory;
use App\Models\JobLocation;
use Illuminate\Http\Request;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class JobApplicantController extends Controller
{
        public function index(Request $request)
        {
            // Base query for published jobs only
            $query = JobPosting::where('is_published', true)
                ->where('application_deadline', '>=', now())
                ->with(['category', 'location']);
    
            // Order featured jobs first, then by newest
            $query->orderBy('is_featured', 'desc')
                   ->orderBy('published_at', 'desc');
    
            // Get all categories and locations for filters
            $categories = JobCategory::orderBy('name')->get();
            $locations = JobLocation::orderBy('country')->orderBy('city')->get();
    
            // Get job listings with pagination
            $jobPostings = $query->paginate(10);
    
            // Featured jobs for highlight section
            $featuredJobs = JobPosting::where('is_published', true)
                ->where('is_featured', true)
                ->where('application_deadline', '>=', now())
                ->with(['category', 'location'])
                ->orderBy('published_at', 'desc')
                ->take(3)
                ->get();
    
            // Statistics for display
            $stats = [
                'total_jobs' => JobPosting::where('is_published', true)
                    ->where('application_deadline', '>=', now())
                    ->count(),
                'total_companies' => JobPosting::where('is_published', true)
                    ->distinct('created_by')
                    ->count('created_by'),
                'recent_jobs' => JobPosting::where('is_published', true)
                    ->where('published_at', '>=', now()->subDays(7))
                    ->count(),
            ];
    
            return view('careers', compact(
                'jobPostings', 
                'featuredJobs', 
                'categories', 
                'locations', 
                'stats'
            ));
        }
    

        public function search(Request $request)
        {
            // Base query for published jobs only
            $query = JobPosting::where('is_published', true)
                ->where('application_deadline', '>=', now())
                ->with(['category', 'location']);
    
            // Apply search query if provided
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('requirements', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('responsibilities', 'LIKE', "%{$searchTerm}%");
                });
            }
    
            // Filter by category
            if ($request->has('category') && !empty($request->category)) {
                $query->where('category_id', $request->category);
            }
    
            // Filter by location
            if ($request->has('location') && !empty($request->location)) {
                $query->where('location_id', $request->location);
            }
    
            // Filter by employment type
            if ($request->has('employment_type') && !empty($request->employment_type)) {
                $query->where('employment_type', $request->employment_type);
            }
    
            // Filter by experience level
            if ($request->has('experience_level') && !empty($request->experience_level)) {
                $query->where('experience_level', $request->experience_level);
            }
    
            // Filter by salary range
            if ($request->has('salary_min') && !empty($request->salary_min)) {
                $query->where('salary_max', '>=', $request->salary_min);
            }
            
            if ($request->has('salary_max') && !empty($request->salary_max)) {
                $query->where('salary_min', '<=', $request->salary_max);
            }
    
            // Sort results
            $sortBy = $request->sort ?? 'newest';
            
            switch ($sortBy) {
                case 'featured':
                    $query->orderBy('is_featured', 'desc')->orderBy('published_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('published_at', 'asc');
                    break;
                case 'salary':
                    $query->orderBy('salary_max', 'desc');
                    break;
                case 'deadline':
                    $query->orderBy('application_deadline', 'asc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('published_at', 'desc');
                    break;
            }
    
            // Get all categories and locations for filters
            $categories = JobCategory::orderBy('name')->get();
            $locations = JobLocation::orderBy('country')->orderBy('city')->get();
            
            // Employment types and experience levels for filters
            $employmentTypes = JobPosting::distinct()->pluck('employment_type');
            $experienceLevels = JobPosting::distinct()->pluck('experience_level');
    
            // Get search results with pagination
            $jobPostings = $query->paginate(10)->withQueryString();
    
            return view('careers', compact(
                'jobPostings', 
                'categories', 
                'locations',
                'employmentTypes',
                'experienceLevels'
            ));
        }
    


        public function show($slug)
        {
            $job = JobPosting::where('slug', $slug)
                ->where('is_published', true)
                ->with(['category', 'location', 'creator'])
                ->firstOrFail();
    
            // Increment view count
            DB::table('job_postings')
                ->where('id', $job->id)
                ->increment('views_count');

            
            
            // Check if the current user has already applied
            $hasApplied = false;
            if (auth()->check()) {
                $hasApplied = JobApplication::where('job_posting_id', $job->id)
                    ->where('user_id', auth()->id())
                    ->exists();
            }
            
            // Related jobs in the same category
            $relatedJobs = JobPosting::where('is_published', true)
                ->where('category_id', $job->category_id)
                ->where('id', '!=', $job->id)
                ->where('application_deadline', '>=', now())
                ->with(['category', 'location'])
                ->take(3)
                ->get();
    
            return view('job-posting', compact('job', 'relatedJobs', 'hasApplied'));
            // return view('job-posting');
        }

        
        public function apply($slug)
        {
            // Find the job posting by slug
            $job = JobPosting::with(['category', 'location'])
                ->where('slug', $slug)
                ->where('is_published', true)
                ->where('application_deadline', '>=', now())
                ->firstOrFail();
            
            // Check if the authenticated user has already applied for this job
            if (auth()->check()) {
                $hasApplied = JobApplication::where('job_posting_id', $job->id)
                    ->where('user_id', auth()->id())
                    ->exists();
                
                if ($hasApplied) {
                    return redirect()->route('jobapplicant.show', $job->slug)
                        ->with('error', 'You have already applied for this position.');
                }
            }
            
            // Increment view count (optional for the application page)
            $job->increment('views_count');
            
            // Get the job details needed for the application form
            $jobData = [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'employment_type' => $job->employment_type,
                'experience_level' => $job->experience_level,
                'salary_min' => $job->salary_min,
                'salary_max' => $job->salary_max,
                'salary_currency' => $job->salary_currency,
                'salary_period' => $job->salary_period,
                'application_deadline' => $job->application_deadline,
                'location' => $job->location,
                'category' => $job->category,
            ];
            
            return view('application-form', [
                'job' => $job,
            ]);
        }


        public function store(Request $request){

            
            // Check if the user has already applied
            if (auth()->check()) {
                $hasApplied = JobApplication::where('job_posting_id', $request->job_posting_id)
                    ->where('user_id', auth()->id())
                    ->exists();
                
                if ($hasApplied) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'You have already applied for this position.');
                }
            }

            // Validate the request data
            $validated = $request->validate([
                'job_posting_id' => 'required|exists:job_postings,id',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|email',
                'phone' => 'required|string|max:30',
                'cover_letter' => 'nullable|string',
                'resume_path' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB max
                'portfolio_url' => 'nullable|url',
                'linkedin_url' => 'nullable|url',
                'github_url' => 'nullable|url',
                'additional_information' => 'nullable|string',
                'skills' => 'nullable|string',
                'current_company' => 'nullable|string',
                'current_position' => 'nullable|string',
                'education' => 'required|string',
                'highest_degree' => 'required|string',
                'expected_salary' => 'nullable|min:30000|numeric',
                'years_of_experience' => 'required|integer',
                'referral_source' => 'nullable|string',
                'terms_agree' => 'required|accepted',
            ]);


            // Handle file upload for resume
            if ($request->hasFile('resume_path')) {
                $resumePath = $request->file('resume_path')->store('resumes', 'public');
                $validated['resume_path'] = $resumePath;
            }
            
            // Add user_id if authenticated
            if (auth()->check()) {
                $validated['user_id'] = auth()->id();
            }
            
            // Create job application
            $application = JobApplication::create($validated);
            
            // Increment application count for the job posting
            JobPosting::where('id', $request->job_posting_id)->increment('applications_count');
            
            // Redirect with success message
            return redirect()->route('careers', $application->id)
                ->with('success', 'Your application has been submitted successfully!');
        }


        public function getEmploymentTypes()
        {
            $types = JobPosting::distinct()->pluck('employment_type');
            return response()->json($types);
        }
    

        public function getExperienceLevels()
        {
            $levels = JobPosting::distinct()->pluck('experience_level');
            return response()->json($levels);
        }
}
