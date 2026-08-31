<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\JobPosting;
use App\Models\JobCategory;
use App\Models\JobLocation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\JobAlertSubscription;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index(){
           // Gather dashboard statistics using Eloquent models
        $dashboardStats = [
                    'active_jobs' => JobPosting::where('is_published', true)
                        ->where('application_deadline', '>=', now())
                        ->count(),
                    
                    'new_applications' => JobApplication::where('status', 'submitted')
                        ->whereNull('reviewed_at')
                        ->count(),
                    
                    'interviews_scheduled' => JobApplication::where('status', 'interview_scheduled')
                        ->count(),
                    
                    'hired_this_month' => JobApplication::where('status', 'hired')
                        ->whereMonth('updated_at', now()->month)
                        ->whereYear('updated_at', now()->year)
                        ->count()
                ];

                // Using Eloquent relationships to join tables and get the needed fields
                    $recentJobs = JobPosting::select([
                        'job_postings.id',
                        'job_postings.title',
                        'job_categories.name as department',
                        'job_locations.city as location',
                        'job_locations.is_remote',
                        'job_postings.applications_count',
                        'job_postings.is_published',
                        'job_postings.application_deadline',
                        'job_postings.created_at'
                    ])
                    ->leftJoin('job_categories', 'job_postings.category_id', '=', 'job_categories.id')
                    ->leftJoin('job_locations', 'job_postings.location_id', '=', 'job_locations.id')
                    ->orderBy('job_postings.created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($job) {
                        // Determine the status label
                        $status = 'Closed';
                        if ($job->is_published && $job->application_deadline >= now()) {
                            $status = 'Active';
                        } elseif ($job->is_published) {
                            $status = 'Pending';
                        }
                        
                        // Format the location
                        $location = $job->is_remote ? 'Remote' : $job->location;
                        
                        return [
                            'id' => $job->id,
                            'title' => $job->title,
                            'department' => $job->department,
                            'location' => $location,
                            'applications' => $job->applications_count,
                            'status' => $status,
                        ];
                    });
                
                // Pass the stats to the view
                return view('admin.dashboard', compact('dashboardStats', 'recentJobs'));
    }


    public function listings(Request $request)
    {
        // Build query with filters
        $query = JobPosting::query()
            ->with(['category', 'location'])
            ->withCount('applications');
        
        // Apply filters if any
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->filled('location')) {
            $query->where('location_id', $request->location);
        }
        
        if ($request->filled('status')) {
            if ($request->status == 'published') {
                $query->where('is_published', true);
            } elseif ($request->status == 'draft') {
                $query->where('is_published', false);
            }
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $query->orderBy($sortField, $sortDirection);
        
        // Get simplePaginated results
        $jobPostings = $query->simplePaginate(10);
        
        // Get categories and locations for filters and form
        $categories = JobCategory::where('is_active', true)->orderBy('name')->get();
        $locations = JobLocation::orderBy('country')->orderBy('city')->get();
        
        return view('admin.job_listings', compact(
            'jobPostings', 
            'categories', 
            'locations'
        ));
    }
    

    public function store(Request $request)
    {
        $request->merge([
            'is_featured' => $request->input('is_featured') === 'on' ? true : false,
            'is_published' => $request->input('is_published') === 'on' ? true : false,
        ]);

        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:job_categories,id',
            'location_id' => 'nullable|exists:job_locations,id',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'responsibilities' => 'required|string',
            'benefits' => 'nullable|string',
            'employment_type' => 'required|string|max:50',
            'experience_level' => 'required|string|max:50',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'salary_period' => 'nullable|string|max:20',
            'application_deadline' => 'required|date|after:today',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ]);
        

        // Create a slug from the title
        $slug = Str::slug($request->title);
        $baseSlug = $slug;
        $counter = 1;
        
        // Ensure slug is unique
        while (JobPosting::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        // Create new job posting
        $jobPosting = new JobPosting();
        $jobPosting->title = $request->title;
        $jobPosting->slug = $slug;
        $jobPosting->category_id = $request->category_id;
        $jobPosting->location_id = $request->location_id;
        $jobPosting->created_by = Auth::id();
        $jobPosting->description = $request->description;
        $jobPosting->requirements = $request->requirements;
        $jobPosting->responsibilities = $request->responsibilities;
        $jobPosting->benefits = $request->benefits;
        $jobPosting->employment_type = $request->employment_type;
        $jobPosting->experience_level = $request->experience_level;
        $jobPosting->salary_min = $request->salary_min;
        $jobPosting->salary_max = $request->salary_max;
        $jobPosting->salary_period = $request->salary_period;
        $jobPosting->application_deadline = $request->application_deadline;
        $jobPosting->is_featured = $request->has('is_featured');
        $jobPosting->is_published = $request->has('is_published');
        
        // Set published_at timestamp if being published
        if ($jobPosting->is_published) {
            $jobPosting->published_at = Carbon::now();
        }

        $jobPosting->save();
        
        // Handle remote location if needed
        if ($request->has('is_remote') && !$request->location_id) {
            // Check if a generic remote location exists or create one
            $remoteLocation = JobLocation::firstOrCreate(
                ['is_remote' => true, 'city' => 'Remote', 'country' => 'Worldwide'],
                ['address' => 'Remote', 'postal_code' => '00000']
            );
            
            $jobPosting->location_id = $remoteLocation->id;
            $jobPosting->save();
        }
        
        return redirect()->route('jobListings')
            ->with('success', 'Job listing created successfully!');
    }
    

    public function show($id)
    {
        $job = JobPosting::with(['category', 'location'])
            ->findOrFail($id);
            
        return view('admin.jobs.show', compact('job'));
    }
    

    public function edit($id)
    {
        $job = JobPosting::findOrFail($id);
        $categories = JobCategory::where('is_active', true)->orderBy('name')->get();
        $locations = JobLocation::orderBy('country')->orderBy('city')->get();
        
        return view('admin.jobs.edit', compact('job', 'categories', 'locations'));
    }
    

    public function update(Request $request, $id)
    {
        // Find the job
        $jobPosting = JobPosting::findOrFail($id);
        
        // Validate the request (similar to store with some differences)
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:job_categories,id',
            'location_id' => 'nullable|exists:job_locations,id',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'responsibilities' => 'required|string',
            'benefits' => 'nullable|string',
            'employment_type' => 'required|string|max:50',
            'experience_level' => 'required|string|max:50',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'salary_period' => 'nullable|string|max:20',
            'application_deadline' => 'required|date',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ]);
        
        // Check if title changed, if so update slug
        if ($jobPosting->title !== $request->title) {
            $slug = Str::slug($request->title);
            $baseSlug = $slug;
            $counter = 1;
            
            // Ensure slug is unique (excluding current job)
            while (JobPosting::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $jobPosting->slug = $slug;
        }
        
        // Update job posting
        $jobPosting->title = $request->title;
        $jobPosting->category_id = $request->category_id;
        $jobPosting->location_id = $request->location_id;
        $jobPosting->description = $request->description;
        $jobPosting->requirements = $request->requirements;
        $jobPosting->responsibilities = $request->responsibilities;
        $jobPosting->benefits = $request->benefits;
        $jobPosting->employment_type = $request->employment_type;
        $jobPosting->experience_level = $request->experience_level;
        $jobPosting->salary_min = $request->salary_min;
        $jobPosting->salary_max = $request->salary_max;
        $jobPosting->salary_period = $request->salary_period;
        $jobPosting->application_deadline = $request->application_deadline;
        $jobPosting->is_featured = $request->has('is_featured');
        
        // Handle publishing status change
        $wasPublished = $jobPosting->is_published;
        $jobPosting->is_published = $request->has('is_published');
        
        if (!$wasPublished && $jobPosting->is_published) {
            // Job is being published for the first time
            $jobPosting->published_at = Carbon::now();
        }
        
        $jobPosting->save();
        
        // Handle remote location if needed
        if ($request->has('is_remote') && !$request->location_id) {
            // Check if a generic remote location exists or create one
            $remoteLocation = JobLocation::firstOrCreate(
                ['is_remote' => true, 'city' => 'Remote', 'country' => 'Worldwide'],
                ['address' => 'Remote', 'postal_code' => '00000']
            );
            
            $jobPosting->location_id = $remoteLocation->id;
            $jobPosting->save();
        }
        
        return redirect()->route('jobListings')
            ->with('success', 'Job listing updated successfully!');
    }

    public function publish($id)
    {
        $job = JobPosting::findOrFail($id);
        $job->is_published = true;
        $job->published_at = Carbon::now();
        $job->save();
        
        return redirect()->back()->with('success', 'Job listing published successfully!');
    }
    

    public function unpublish($id)
    {
        $job = JobPosting::findOrFail($id);
        $job->is_published = false;
        $job->save();
        
        return redirect()->back()->with('success', 'Job listing unpublished!');
    }
    
    /**
     * Remove the specified job posting from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $job = JobPosting::findOrFail($id);
        $job->delete();
        
        return redirect()->route('jobListings')
            ->with('success', 'Job listing deleted successfully!');
    }

    /**
     * Job-alert subscribers. Read-only: subscriptions are created and removed
     * by the subscriber through the emailed links, never by staff.
     */
    public function jobAlerts(Request $request)
    {
        $query = JobAlertSubscription::with(['category', 'location'])->latest();

        if ($request->input('status') === 'confirmed') {
            $query->where('is_confirmed', true);
        } elseif ($request->input('status') === 'pending') {
            $query->where('is_confirmed', false);
        }

        if ($search = trim((string) $request->input('q'))) {
            $query->where('email', 'like', '%' . $search . '%');
        }

        if ($request->input('export') === 'csv') {
            return $this->exportJobAlerts($query->get());
        }

        return view('admin.job_alerts', [
            'subscriptions' => $query->paginate(50)->withQueryString(),
            'confirmedCount' => JobAlertSubscription::where('is_confirmed', true)->count(),
            'pendingCount' => JobAlertSubscription::where('is_confirmed', false)->count(),
            'status' => $request->input('status', ''),
            'search' => $search,
        ]);
    }

    /**
     * Streamed so a large list never has to be held in memory at once.
     */
    private function exportJobAlerts($subscriptions): StreamedResponse
    {
        $filename = 'job-alert-subscribers-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($subscriptions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Email', 'Category', 'Location', 'Keywords',
                'Confirmed', 'Confirmed at', 'Subscribed at',
            ]);

            foreach ($subscriptions as $subscription) {
                fputcsv($handle, [
                    $subscription->email,
                    $subscription->category?->name ?? 'Any',
                    $subscription->location
                        ? ($subscription->location->is_remote ? 'Remote' : $subscription->location->city)
                        : 'Any',
                    $subscription->keywords ?? '',
                    $subscription->is_confirmed ? 'Yes' : 'No',
                    $subscription->confirmed_at?->format('Y-m-d H:i') ?? '',
                    $subscription->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
