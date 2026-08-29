<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\JobPosting;
use App\Models\JobCategory;
use App\Models\JobLocation;
use Illuminate\Http\Request;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApplicationConfirmation;
use App\Models\User;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Support\JobPostingSchema;

class JobApplicantController extends Controller
{
    /**
     * Sort options the board accepts. Anything else falls back to 'newest'.
     */
    private const SORTS = ['newest', 'oldest', 'featured', 'salary', 'deadline'];

    /**
     * The job board. Filtering, sorting and pagination all read from the
     * query string, so every board state is a shareable, crawlable URL.
     */
    public function index(Request $request)
    {
        $query = JobPosting::active()->with(['category', 'location']);

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($inner) use ($q) {
                $inner->where('title', 'LIKE', "%{$q}%")
                    ->orWhere('description', 'LIKE', "%{$q}%")
                    ->orWhere('requirements', 'LIKE', "%{$q}%")
                    ->orWhere('responsibilities', 'LIKE', "%{$q}%");
            });
        }

        foreach (['category' => 'category_id', 'location' => 'location_id'] as $param => $column) {
            if ($request->filled($param)) {
                $query->where($column, $request->query($param));
            }
        }

        foreach (['employment_type', 'experience_level'] as $param) {
            if ($request->filled($param)) {
                $query->where($param, $request->query($param));
            }
        }

        // Salary overlap: show roles whose top of band reaches the asked-for floor.
        if ($request->filled('salary_min')) {
            $query->where('salary_max', '>=', $request->query('salary_min'));
        }

        $sort = in_array($request->query('sort'), self::SORTS, true)
            ? $request->query('sort')
            : 'newest';

        switch ($sort) {
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
            default:
                $query->orderBy('is_featured', 'desc')->orderBy('published_at', 'desc');
                break;
        }

        $jobPostings = $query->paginate(9)->withQueryString();

        $categories = JobCategory::orderBy('name')->get();
        $locations = JobLocation::orderBy('country')->orderBy('city')->get();

        // Filter vocabularies come from live, applyable roles only — never from
        // expired or unpublished rows, which would offer dead-end options.
        $employmentTypes = JobPosting::active()
            ->distinct()
            ->orderBy('employment_type')
            ->pluck('employment_type');

        $experienceLevels = JobPosting::active()
            ->distinct()
            ->orderBy('experience_level')
            ->pluck('experience_level');

        $featuredJobs = JobPosting::active()
            ->featured()
            ->with(['category', 'location'])
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        return view('careers', [
            'jobPostings' => $jobPostings,
            'featuredJobs' => $featuredJobs,
            'categories' => $categories,
            'locations' => $locations,
            'employmentTypes' => $employmentTypes,
            'experienceLevels' => $experienceLevels,
            'filters' => $this->activeFilters($request, $categories, $locations),
            'hasFilters' => $this->hasFilters($request),
            'sort' => $sort,
        ]);
    }

    /**
     * Whether any filter (not just sorting or paging) is applied. Drives the
     * featured strip, which only makes sense on an unfiltered board.
     */
    private function hasFilters(Request $request): bool
    {
        foreach (['q', 'category', 'location', 'employment_type', 'experience_level', 'salary_min'] as $param) {
            if ($request->filled($param)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build the removable filter chips: a human label plus the URL for the
     * same board with just that one filter dropped.
     */
    private function activeFilters(Request $request, $categories, $locations): array
    {
        $chips = [];
        $current = $request->query();

        $label = function (string $param) use ($request, $categories, $locations): ?string {
            $value = $request->query($param);

            switch ($param) {
                case 'q':
                    return '"' . $value . '"';
                case 'category':
                    return optional($categories->firstWhere('id', (int) $value))->name;
                case 'location':
                    $location = $locations->firstWhere('id', (int) $value);
                    if (! $location) {
                        return null;
                    }
                    return $location->is_remote
                        ? 'Remote'
                        : trim($location->city . ', ' . $location->country, ', ');
                case 'salary_min':
                    return 'From $' . number_format((float) $value);
                default:
                    return $value;
            }
        };

        foreach (['q', 'category', 'location', 'employment_type', 'experience_level', 'salary_min'] as $param) {
            if (! $request->filled($param)) {
                continue;
            }

            $text = $label($param);
            if ($text === null) {
                continue;
            }

            $without = $current;
            unset($without[$param], $without['page']);

            $chips[] = [
                'label' => $text,
                'remove_url' => route('careers', $without),
            ];
        }

        return $chips;
    }

    /**
     * A single job posting.
     */
    public function show($slug)
    {
        $job = JobPosting::where('slug', $slug)
            ->published()
            ->with(['category', 'location'])
            ->firstOrFail();

        // Query-builder increment so the view count never touches updated_at.
        DB::table('job_postings')->where('id', $job->id)->increment('views_count');

        $hasApplied = false;
        if (auth()->check()) {
            $hasApplied = JobApplication::where('job_posting_id', $job->id)
                ->where('user_id', auth()->id())
                ->exists();
        }

        $relatedJobs = JobPosting::active()
            ->where('category_id', $job->category_id)
            ->where('id', '!=', $job->id)
            ->with(['category', 'location'])
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        return view('job-posting', [
            'job' => $job,
            'relatedJobs' => $relatedJobs,
            'hasApplied' => $hasApplied,
            'jsonLd' => JobPostingSchema::for($job),
        ]);
    }

    /**
     * The application form for a job that is still open. Open to guests —
     * an account is offered after applying, never required to apply.
     */
    public function apply($slug)
    {
        $job = JobPosting::with(['category', 'location'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        if (auth()->check() && $this->alreadyApplied($job, auth()->user()->email)) {
            return redirect()->route('careers.show', $job->slug)
                ->with('error', 'You have already applied for this position.');
        }

        return view('application-form', [
            'job' => $job,
        ]);
    }

    /**
     * Has this job already been applied to by this user or this email address?
     * Checking the email as well as the account is what stops a guest
     * submitting the same application repeatedly.
     */
    private function alreadyApplied(JobPosting $job, ?string $email): bool
    {
        return JobApplication::where('job_posting_id', $job->id)
            ->where(function ($query) use ($email) {
                if (auth()->check()) {
                    $query->where('user_id', auth()->id());
                }

                if ($email) {
                    $query->orWhere('email', $email);
                }
            })
            ->exists();
    }

    /**
     * Persist an application against the job in the URL.
     */
    public function store(StoreJobApplicationRequest $request, $slug)
    {
        $job = JobPosting::where('slug', $slug)->active()->firstOrFail();

        // Bots get one neutral response and no detail about which check caught
        // them. Never a fake reference number: with nothing persisted there is
        // no reference to show, and inventing one risks handing out a value
        // belonging to a real applicant.
        if ($request->looksAutomated()) {
            return redirect()->route('careers.show', $job->slug)
                ->with('error', "Thanks — we couldn't process that submission. Please try again.");
        }

        if ($this->alreadyApplied($job, $request->input('email'))) {
            return redirect()->route('careers.show', $job->slug)
                ->with('error', 'An application for this role has already been submitted with that email address.');
        }

        $data = $request->applicationData();
        $data['job_posting_id'] = $job->id;
        $data['resume_path'] = $request->file('resume_path')->store('resumes', 'public');

        if (auth()->check()) {
            $data['user_id'] = auth()->id();
        }

        $application = JobApplication::create($data);

        JobPosting::where('id', $job->id)->increment('applications_count');

        try {
            Mail::to($application->email)
                ->send(new ApplicationConfirmation($application, $job));
        } catch (\Exception $e) {
            // A mail failure must never lose an application that is already saved.
            Log::error('Failed to send application confirmation email: ' . $e->getMessage());
        }

        // Held for the confirmation page and the account-claim guard. The email
        // is what claim-account checks the posted address against.
        session()->put('recent_application', [
            'id' => $application->id,
            'reference' => $application->reference,
            'email' => $application->email,
            'first_name' => $application->first_name,
            'last_name' => $application->last_name,
            'job_slug' => $job->slug,
        ]);

        return redirect()->route('careers.applied', $job->slug);
    }

    /**
     * Confirmation page. Reachable only straight after a successful submit —
     * it is not a page anyone can navigate to for someone else's reference.
     */
    public function applied($slug)
    {
        $job = JobPosting::where('slug', $slug)->published()->firstOrFail();
        $recent = session('recent_application');

        if (! $recent || ($recent['job_slug'] ?? null) !== $job->slug) {
            return redirect()->route('careers.show', $job->slug);
        }

        // Offer the account only when it could actually be created.
        $canClaim = ! auth()->check()
            && ! User::where('email', $recent['email'])->exists();

        return view('application-confirmed', [
            'job' => $job,
            'application' => $recent,
            'canClaim' => $canClaim,
        ]);
    }
}
