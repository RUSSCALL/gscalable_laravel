<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InterviewScheduled;
use App\Models\Interview;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class InterviewController extends Controller
{
    // Statuses an interview can move an application forward from; later stages are left alone.
    private const PRE_INTERVIEW = ['submitted', 'under_review', 'shortlisted'];

    public function index(Request $request)
    {
        $status = array_key_exists((string) $request->query('status'), Interview::STATUSES) ? $request->query('status') : null;

        $query = Interview::with(['application.jobPosting:id,title']);

        if ($status) {
            $query->where('status', $status)->orderByDesc('scheduled_at');
        } else {
            $query->upcoming()->orderBy('scheduled_at');
        }

        return view('admin.interviews.index', [
            'interviews' => $query->paginate(25)->withQueryString(),
            'status' => $status,
            // Candidates still in play, for the "schedule an interview" form.
            'candidates' => JobApplication::with('jobPosting:id,title')
                ->whereNotIn('status', ['rejected', 'offer_declined', 'hired'])
                ->latest()
                ->limit(200)
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'job_application_id' => ['required', 'integer', 'exists:job_applications,id'],
            // Times are stored as typed in the chosen zone, so compare dates rather than against the server's UTC clock.
            'scheduled_at' => ['required', 'date', 'after_or_equal:today'],
            'timezone' => ['required', Rule::in(array_keys(Interview::TIMEZONES))],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'type' => ['required', Rule::in(array_keys(Interview::TYPES))],
            'location' => ['nullable', 'string', 'max:255'],
            'interviewers' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'notify_candidate' => ['nullable', 'boolean'],
        ], [
            'scheduled_at.after_or_equal' => 'Pick today or a later date.',
        ]);

        $interview = new Interview(collect($data)->except('notify_candidate')->all());
        $interview->created_by = $request->user()->id;
        $interview->save();

        $application = $interview->application;
        if (in_array($application->status, self::PRE_INTERVIEW, true)) {
            $application->forceFill(['status' => 'interview_scheduled'])->save();
        }

        $message = 'Interview scheduled.';

        if ($request->boolean('notify_candidate')) {
            try {
                $admin = $request->user();
                Mail::to($application->email)->send(new InterviewScheduled($interview, new Address($admin->email, $admin->name)));
                $message = 'Interview scheduled and the candidate has been emailed.';
            } catch (\Exception $e) {
                Log::error('Failed to send interview invitation: ' . $e->getMessage());
                $message = 'Interview scheduled, but the email to the candidate could not be sent.';
            }
        }

        return back()->with('success', $message);
    }

    public function update(Request $request, Interview $interview)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['completed', 'cancelled'])],
        ]);

        $interview->update($data);

        if ($data['status'] === 'completed' && $interview->application->status === 'interview_scheduled') {
            $interview->application->forceFill(['status' => 'interviewed'])->save();
        }

        return back()->with('success', 'Interview marked ' . $data['status'] . '.');
    }
}
