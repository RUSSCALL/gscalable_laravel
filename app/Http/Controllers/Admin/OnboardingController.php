<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\OnboardingTaskCompletion;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function index()
    {
        return view('admin.onboarding.index', [
            'hires' => JobApplication::with(['jobPosting:id,title', 'onboardingCompletions'])
                ->where('status', 'hired')
                ->latest('updated_at')
                ->paginate(20),
            'tasks' => OnboardingTaskCompletion::TASKS,
        ]);
    }

    public function toggle(Request $request, JobApplication $application, string $task)
    {
        abort_unless(array_key_exists($task, OnboardingTaskCompletion::TASKS) && $application->status === 'hired', 404);

        $completion = $application->onboardingCompletions()->where('task', $task)->first();

        if ($completion) {
            $completion->delete();
        } else {
            $completion = $application->onboardingCompletions()->make(['task' => $task]);
            $completion->completed_by = $request->user()->id;
            $completion->save();
        }

        return back();
    }
}
