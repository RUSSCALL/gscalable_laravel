<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApplicantController extends Controller
{
    public function index(Request $request)
    {
        $query = JobApplication::with('jobPosting:id,title');

        if ($request->filled('job')) {
            $query->where('job_posting_id', $request->query('job'));
        }

        if (in_array($request->query('status'), JobApplication::STATUSES, true)) {
            $query->where('status', $request->query('status'));
        }

        if ($request->query('rank') === 'unranked') {
            $query->whereNull('screener_rank');
        } elseif (array_key_exists((string) $request->query('rank'), JobApplication::RANKS)) {
            $query->where('screener_rank', $request->query('rank'));
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($inner) use ($search) {
                $inner->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if ($request->query('sort') === 'rank') {
            $query->orderByRaw(self::rankOrder())->latest();
        } else {
            $query->latest();
        }

        return view('admin.applicants.index', [
            'applications' => $query->paginate(25)->withQueryString(),
            'jobs' => JobPosting::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function show(JobApplication $application)
    {
        // Opening an application is what takes it out of the dashboard's "New Applications" count.
        if (! $application->reviewed_at) {
            $application->forceFill(['reviewed_at' => now(), 'reviewed_by' => auth()->id()])->save();
        }

        $application->load(['jobPosting', 'attachments', 'screenerAnswers', 'interviews.creator', 'messages.sender']);

        return view('admin.applicants.show', [
            'application' => $application,
            'interviewTypes' => Interview::TYPES,
        ]);
    }

    public function updateStatus(Request $request, JobApplication $application)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(JobApplication::STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $application->fill($data)->save();

        return back()->with('success', 'Application updated.');
    }

    public function download(JobApplication $application, string $file)
    {
        [$path, $name] = match ($file) {
            'resume' => [$application->resume_path, 'Resume'],
            'cover-letter' => [$application->cover_letter_path, 'Cover letter'],
            default => (function () use ($application, $file) {
                $attachment = $application->attachments()->findOrFail($file);

                return [$attachment->path, pathinfo($attachment->original_name, PATHINFO_FILENAME)];
            })(),
        };

        $disk = JobApplication::disk();
        abort_unless($path && $disk->exists($path), 404);

        $downloadName = Str::slug($name . ' ' . $application->full_name) . '.' . pathinfo($path, PATHINFO_EXTENSION);

        return $disk->download($path, $downloadName);
    }

    public static function rankOrder(): string
    {
        $cases = collect(array_keys(JobApplication::RANKS))
            ->map(fn ($rank, $i) => "WHEN '{$rank}' THEN {$i}")
            ->implode(' ');

        return "CASE screener_rank {$cases} ELSE 99 END";
    }
}
