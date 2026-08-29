<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Read-only view of an applicant's own applications and their statuses.
 */
class ApplicantDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Scoped through the relation, so an applicant can only ever see rows
        // belonging to their own account.
        $applications = $user->jobApplications()
            ->with(['jobPosting:id,title,slug,employment_type,application_deadline'])
            ->get();

        // Applications whose outcome is settled are separated from live ones,
        // so the list leads with what the applicant can still act on.
        [$closed, $active] = $applications->partition(
            fn ($application) => $application->status_tone === 'closed'
        );

        return view('applicant.dashboard', [
            'activeApplications' => $active,
            'closedApplications' => $closed,
            'totalCount' => $applications->count(),
        ]);
    }
}
