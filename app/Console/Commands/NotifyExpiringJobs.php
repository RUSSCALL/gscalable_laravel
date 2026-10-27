<?php

namespace App\Console\Commands;

use App\Models\JobPosting;
use App\Models\User;
use App\Notifications\JobExpiringSoon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class NotifyExpiringJobs extends Command
{
    protected $signature = 'jobs:notify-expiring {--days=3 : Warn this many days before the deadline}';

    protected $description = 'Notify admins about live job postings whose deadline is close';

    public function handle(): int
    {
        $jobs = JobPosting::active()
            ->whereDate('application_deadline', '<=', today()->addDays((int) $this->option('days')))
            ->whereNull('expiry_notified_at')
            ->get();

        if ($jobs->isEmpty()) {
            return self::SUCCESS;
        }

        $admins = User::admins()->get();

        foreach ($jobs as $job) {
            Notification::send($admins, new JobExpiringSoon($job));
            // Query builder so the job's updated_at (shown as recent activity) is left alone.
            DB::table('job_postings')->where('id', $job->id)->update(['expiry_notified_at' => now()]);
        }

        $this->info("Notified admins about {$jobs->count()} expiring job(s).");

        return self::SUCCESS;
    }
}
