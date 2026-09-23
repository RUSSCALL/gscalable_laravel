<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Deletes accounts that never confirmed their email within the grace period.
 *
 * job_applications.user_id is nullable/nullOnDelete, so a pruned user's
 * applications survive as guest applications instead of being removed.
 */
class PruneUnverifiedUsers extends Command
{
    protected $signature = 'users:prune-unverified {--hours=48 : Age threshold for an unverified account}';

    protected $description = 'Delete unverified user accounts older than the given threshold';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');

        $count = User::whereNull('email_verified_at')
            ->where('created_at', '<', now()->subHours($hours))
            ->delete();

        if ($count > 0) {
            $this->info("Pruned {$count} unverified user(s) older than {$hours} hours.");
        }

        return self::SUCCESS;
    }
}
