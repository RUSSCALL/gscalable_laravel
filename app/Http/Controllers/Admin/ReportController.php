<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DisabilitySelfIdentification;
use App\Models\Interview;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $this->date($request->query('from'));
        $to = $this->date($request->query('to'));

        $applications = JobApplication::query()
            ->when($from, fn ($q) => $q->where('job_applications.created_at', '>=', $from->startOfDay()))
            ->when($to, fn ($q) => $q->where('job_applications.created_at', '<=', $to->copy()->endOfDay()));

        if ($request->query('export') === 'csv') {
            return $this->export((clone $applications)->with('jobPosting:id,title')->orderBy('created_at')->get());
        }

        $countBy = fn (string $column) => (clone $applications)
            ->selectRaw("{$column} as value, count(*) as total")
            ->groupBy($column)
            ->orderByDesc('total')
            ->pluck('total', 'value');

        $byJob = (clone $applications)
            ->join('job_postings', 'job_postings.id', '=', 'job_applications.job_posting_id')
            ->selectRaw("job_postings.id, job_postings.title, count(*) as total,
                sum(case when screener_rank = 'top_fit' then 1 else 0 end) as top_fit,
                sum(case when screener_rank = 'fit' then 1 else 0 end) as fit,
                sum(case when screener_rank = 'suitable' then 1 else 0 end) as suitable,
                sum(case when screener_rank = 'not_fit' then 1 else 0 end) as not_fit,
                sum(case when job_applications.status = 'hired' then 1 else 0 end) as hired")
            ->groupBy('job_postings.id', 'job_postings.title')
            ->orderByDesc('total')
            ->get();

        // Aggregates only: individual answers are never shown to anyone reviewing applications.
        $disability = DisabilitySelfIdentification::whereIn('job_application_id', (clone $applications)->select('job_applications.id'))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'total' => (clone $applications)->count(),
            'hired' => (clone $applications)->where('status', 'hired')->count(),
            'interviews' => Interview::whereIn('job_application_id', (clone $applications)->select('job_applications.id'))->count(),
            'byJob' => $byJob,
            'byStatus' => $countBy('status'),
            'bySource' => $countBy('referral_source'),
            'byAuthorization' => $countBy('work_authorization'),
            'byAvailability' => $countBy('availability'),
            'disability' => $disability,
        ]);
    }

    private function date(?string $value): ?Carbon
    {
        try {
            return $value ? Carbon::createFromFormat('Y-m-d', $value) : null;
        } catch (\Exception) {
            return null;
        }
    }

    private function export($applications): StreamedResponse
    {
        return response()->streamDownload(function () use ($applications) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Reference', 'Applied', 'Job', 'First name', 'Last name', 'Email', 'Phone', 'Current location',
                'Status', 'Rank', 'Years of experience', 'Availability', 'Work authorization',
                'Willing to relocate', 'Referral source',
            ]);

            foreach ($applications as $application) {
                fputcsv($handle, [
                    $application->reference,
                    $application->created_at->format('Y-m-d'),
                    $application->jobPosting?->title,
                    $application->first_name,
                    $application->last_name,
                    $application->email,
                    $application->phone,
                    $application->current_location,
                    $application->status_label,
                    $application->rank_label ?? 'Not ranked',
                    $application->years_of_experience,
                    $application->availability_label,
                    $application->work_authorization_label,
                    $application->willing_to_relocate === null ? '' : ($application->willing_to_relocate ? 'Yes' : 'No'),
                    $application->referral_source,
                ]);
            }

            fclose($handle);
        }, 'applications-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
