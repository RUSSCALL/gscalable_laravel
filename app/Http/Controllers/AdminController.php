<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Models\JobPosting;
use App\Models\JobCategory;
use App\Models\JobLocation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\Interview;
use App\Models\JobAlertSubscription;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\JobPostingRequest;

class AdminController extends Controller
{
    public const LISTING_FILTERS = [
        'featured' => 'Featured jobs',
        'most_applications' => 'Most applications',
        'recent' => 'Recently added (30 days)',
        'expiring' => 'Expiring soon (7 days)',
    ];

    public function index(){
           // Gather dashboard statistics using Eloquent models
        $dashboardStats = [
                    'active_jobs' => JobPosting::where('is_published', true)
                        ->whereDate('application_deadline', '>=', today())
                        ->count(),
                    
                    'new_applications' => JobApplication::where('status', 'submitted')
                        ->whereNull('reviewed_at')
                        ->count(),
                    
                    'interviews_scheduled' => Interview::upcoming()->count(),
                    
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
                        'job_postings.work_arrangement',
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
                        $status = 'Draft';
                        if ($job->is_published && ! $job->application_deadline->isBefore(today())) {
                            $status = 'Active';
                        } elseif ($job->is_published) {
                            $status = 'Expired';
                        }
                        
                        $location = $job->is_remote || $job->work_arrangement === 'remote' ? 'Remote' : $job->location;
                        if ($job->work_arrangement === 'hybrid') {
                            $location = trim($location . ' (Hybrid)');
                        }

                        return [
                            'id' => $job->id,
                            'title' => $job->title,
                            'department' => $job->department ?? 'Uncategorized',
                            'location' => $location,
                            'applications' => $job->applications_count,
                            'status' => $status,
                        ];
                    });
                
                return view('admin.dashboard', [
                    'dashboardStats' => $dashboardStats,
                    'recentJobs' => $recentJobs,
                    'recentActivity' => $this->recentActivity(),
                ]);
    }

    private function recentActivity()
    {
        $applications = JobApplication::with('jobPosting:id,title')->latest()->limit(5)->get()
            ->map(fn ($application) => [
                'icon' => 'bi-person-plus text-primary',
                'text' => 'New application for',
                'subject' => $application->jobPosting->title ?? 'a deleted job',
                'url' => route('admin.applicants.show', $application),
                'at' => $application->created_at,
            ]);

        $interviews = Interview::with('application.jobPosting:id,title')->latest()->limit(5)->get()
            ->map(fn ($interview) => [
                'icon' => 'bi-calendar-check text-success',
                'text' => 'Interview scheduled for',
                'subject' => $interview->application->jobPosting->title ?? 'a deleted job',
                'url' => route('admin.applicants.show', $interview->job_application_id),
                'at' => $interview->created_at,
            ]);

        $jobs = JobPosting::latest('updated_at')->limit(5)->get(['id', 'title', 'created_at', 'updated_at'])
            ->map(fn ($job) => [
                'icon' => 'bi-pencil-square text-warning',
                'text' => $job->updated_at->eq($job->created_at) ? 'Job listing created:' : 'Job listing updated:',
                'subject' => $job->title,
                'url' => route('jobs.show', $job->id),
                'at' => $job->updated_at,
            ]);

        return $applications->concat($interviews)->concat($jobs)->sortByDesc('at')->take(5)->values();
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
        
        $filter = array_key_exists((string) $request->get('filter'), self::LISTING_FILTERS) ? $request->get('filter') : null;

        switch ($filter) {
            case 'featured':
                $query->where('is_featured', true);
                break;
            case 'most_applications':
                $query->orderByDesc(JobApplication::selectRaw('count(*)')->whereColumn('job_applications.job_posting_id', 'job_postings.id'));
                break;
            case 'recent':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
            case 'expiring':
                $query->active()
                    ->whereDate('application_deadline', '<=', today()->addDays(7))
                    ->orderBy('application_deadline');
                break;
        }

        // Apply sorting
        $sortField = in_array($request->get('sort'), ['created_at', 'title', 'application_deadline', 'published_at'], true)
            ? $request->get('sort')
            : 'created_at';
        $sortDirection = $request->get('direction') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortField, $sortDirection);

        if ($request->get('export') === 'csv') {
            return $this->exportListings($query->get());
        }

        // Get simplePaginated results
        $jobPostings = $query->simplePaginate(10)->withQueryString();
        
        // Get categories and locations for filters and form
        $categories = JobCategory::where('is_active', true)->orderBy('name')->get();
        $locations = JobLocation::orderBy('country')->orderBy('city')->get();
        
        return view('admin.job_listings', compact(
            'jobPostings',
            'categories',
            'locations',
            'filter'
        ));
    }

    private function exportListings($jobs): StreamedResponse
    {
        return response()->streamDownload(function () use ($jobs) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Title', 'Category', 'Location', 'Work arrangement', 'Employment type', 'Experience level',
                'Applications', 'Deadline', 'Status', 'Featured', 'Created',
            ]);

            foreach ($jobs as $job) {
                fputcsv($handle, [
                    $job->title,
                    $job->category->name ?? 'Uncategorized',
                    $job->location_label,
                    $job->work_arrangement_label,
                    $job->employment_type,
                    $job->experience_level,
                    $job->applications_count,
                    $job->application_deadline->format('Y-m-d'),
                    $job->is_published ? ($job->application_deadline->isBefore(today()) ? 'Expired' : 'Published') : 'Draft',
                    $job->is_featured ? 'Yes' : 'No',
                    $job->created_at->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, 'job-listings-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
    

    public function create()
    {
        return view('admin.jobs.create', $this->formOptions() + ['job' => new JobPosting()]);
    }

    /**
     * Render the unsaved job with the public job page so the admin sees exactly
     * what applicants will. Nothing is written until they confirm from here.
     */
    public function preview(JobPostingRequest $request, $id = null)
    {
        $job = $id ? JobPosting::findOrFail($id) : (new JobPosting())->forceFill(['views_count' => 0, 'applications_count' => 0]);
        $this->fillFromRequest($job, $request, persist: false);

        // Flattened to "screener_questions[0][question]" so nested rows survive as hidden inputs.
        $fields = collect(Arr::dot($request->validated()))
            ->reject(fn ($value) => is_array($value))
            ->mapWithKeys(fn ($value, $key) => [
                preg_replace('/\.([^.]+)/', '[$1]', $key) => is_bool($value) ? (int) $value : $value,
            ])
            ->all();

        return view('job-posting', [
            'job' => $job,
            'relatedJobs' => collect(),
            'hasApplied' => false,
            'jsonLd' => null,
            'preview' => [
                'action' => $id ? route('jobs.update', $id) : route('jobs.store'),
                'method' => $id ? 'PUT' : 'POST',
                'fields' => $fields,
            ],
        ]);
    }

    public function store(JobPostingRequest $request)
    {
        if ($request->input('intent') === 'edit') {
            return redirect()->route('jobs.create')->withInput();
        }

        $job = new JobPosting();
        $job->created_by = Auth::id();
        $this->fillFromRequest($job, $request, persist: true);
        $job->save();
        $this->syncScreenerQuestions($job, $request->validated('screener_questions', []));

        return redirect()->route('jobListings')->with('success', $job->is_published
            ? 'Job listing created and published.'
            : 'Job listing saved as a draft.');
    }

    public function show($id)
    {
        $job = JobPosting::with(['category', 'location', 'creator', 'screenerQuestions'])
            ->withCount('applications')
            ->findOrFail($id);

        return view('admin.jobs.show', compact('job'));
    }

    public function edit($id)
    {
        return view('admin.jobs.edit', $this->formOptions() + ['job' => JobPosting::findOrFail($id)]);
    }

    public function update(JobPostingRequest $request, $id)
    {
        $job = JobPosting::findOrFail($id);

        if ($request->input('intent') === 'edit') {
            return redirect()->route('jobs.edit', $job->id)->withInput();
        }

        $this->fillFromRequest($job, $request, persist: true);
        $job->save();
        $this->syncScreenerQuestions($job, $request->validated('screener_questions', []));

        return redirect()->route('jobListings')->with('success', 'Job listing updated.');
    }

    public function publish($id)
    {
        $job = JobPosting::findOrFail($id);

        if ($job->application_deadline->isBefore(today())) {
            return redirect()->back()->with('error', 'Extend the application deadline before publishing this job.');
        }

        $this->setPublished($job, true);
        $job->save();

        return redirect()->back()->with('success', 'Job listing published.');
    }

    public function unpublish($id)
    {
        $job = JobPosting::findOrFail($id);
        $this->setPublished($job, false);
        $job->save();

        return redirect()->back()->with('success', 'Job listing unpublished.');
    }

    public function destroy($id)
    {
        JobPosting::findOrFail($id)->delete();

        return redirect()->route('jobListings')->with('success', 'Job listing deleted.');
    }

    private function formOptions(): array
    {
        return [
            'categories' => JobCategory::where('is_active', true)->orderBy('name')->get(),
            'locations' => JobLocation::orderBy('country')->orderBy('city')->get(),
        ];
    }

    /**
     * Apply the form to the job. With $persist false (preview), a typed-in new
     * category or location is built in memory only and nothing is saved.
     */
    private function fillFromRequest(JobPosting $job, JobPostingRequest $request, bool $persist): void
    {
        $data = $request->validated();

        if ($job->title !== $data['title'] || ! $job->slug) {
            $job->slug = $this->uniqueSlug(JobPosting::class, $data['title'], $job->id);
        }

        $category = $this->resolveCategory($data['category_id'] ?? null, $persist);
        $location = $data['is_remote'] && empty($data['location_id'])
            ? $this->remoteLocation($persist)
            : $this->resolveLocation($data['location_id'] ?? null, $persist);

        $job->fill(collect($data)->except(['category_id', 'location_id', 'is_remote', 'is_published', 'screener_questions'])->all());
        $job->category_id = $category?->id;
        $job->location_id = $location?->id;
        $job->setRelation('category', $category);
        $job->setRelation('location', $location);
        $this->setPublished($job, $data['is_published']);

        // A new deadline earns a fresh "expiring soon" warning.
        if ($job->isDirty('application_deadline')) {
            $job->expiry_notified_at = null;
        }
    }

    // Existing applications keep their own copy of each question, so replacing the set is safe.
    private function syncScreenerQuestions(JobPosting $job, array $rows): void
    {
        $job->screenerQuestions()->delete();

        foreach (array_values($rows) as $position => $row) {
            $job->screenerQuestions()->create([
                'question' => trim($row['question']),
                'importance' => $row['importance'],
                'required_answer' => (bool) $row['required_answer'],
                'position' => $position,
            ]);
        }
    }

    private function resolveCategory(?string $value, bool $persist): ?JobCategory
    {
        $name = JobPostingRequest::newName($value);

        if ($name === null) {
            return $value ? JobCategory::find($value) : null;
        }

        $category = JobCategory::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first()
            ?? new JobCategory(['name' => $name, 'is_active' => true]);

        if ($persist && ! $category->exists) {
            $category->slug = $this->uniqueSlug(JobCategory::class, $name);
            $category->save();
        }

        return $category;
    }

    private function resolveLocation(?string $value, bool $persist): ?JobLocation
    {
        $label = JobPostingRequest::newName($value);

        if ($label === null) {
            return $value ? JobLocation::find($value) : null;
        }

        $parts = JobLocation::parseLabel($label);
        $location = JobLocation::whereRaw('LOWER(city) = ? AND LOWER(country) = ?', [mb_strtolower($parts['city']), mb_strtolower($parts['country'])])
            ->where('is_remote', false)
            ->first()
            ?? new JobLocation($parts + ['is_remote' => false]);

        if ($persist && ! $location->exists) {
            $location->save();
        }

        return $location;
    }

    private function remoteLocation(bool $persist): JobLocation
    {
        $location = JobLocation::firstOrNew(
            ['is_remote' => true, 'city' => 'Remote', 'country' => 'Worldwide'],
            ['address' => 'Remote', 'postal_code' => '00000']
        );

        if ($persist && ! $location->exists) {
            $location->save();
        }

        return $location;
    }

    private function setPublished(JobPosting $job, bool $published): void
    {
        // published_at marks when the current run went live; re-publishing restarts it.
        if ($published && ! $job->is_published) {
            $job->published_at = Carbon::now();
        }

        $job->is_published = $published;
    }

    /** @param class-string<\Illuminate\Database\Eloquent\Model> $model */
    private function uniqueSlug(string $model, string $text, ?int $ignoreId = null): string
    {
        // Both slug columns are 100 chars; leave room for a "-N" suffix.
        $base = rtrim(Str::limit(Str::slug($text), 90, ''), '-') ?: 'item';
        $slug = $base;

        for ($n = 2; $model::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists(); $n++) {
            $slug = "{$base}-{$n}";
        }

        return $slug;
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
