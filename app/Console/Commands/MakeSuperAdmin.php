<?php

namespace App\Console\Commands;

use App\Actions\AssignRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * The only way to grant SuperAdmin: the roles page deliberately can't, so a
 * hijacked SuperAdmin session can't mint more of them.
 */
class MakeSuperAdmin extends Command
{
    protected $signature = 'user:make-superadmin {email : Email of an existing account}';

    protected $description = 'Give an existing user the SuperAdmin role';

    public function handle(AssignRole $assignRole): int
    {
        $email = trim($this->argument('email'));
        $user = User::with('role')->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->first();

        if (! $user) {
            $this->error("No account found for {$email}. They need to register first.");

            return self::FAILURE;
        }

        $current = $user->role?->role_name ?? 'no role';

        if ($current === Role::SUPER_ADMIN) {
            $this->info("{$user->email} is already a SuperAdmin.");

            return self::SUCCESS;
        }

        if (! $user->hasVerifiedEmail()) {
            $this->warn("{$user->email} has not verified their email; they can't open the admin area until they do.");
        }

        if (! $this->confirm("Make {$user->name} <{$user->email}> a SuperAdmin? (currently: {$current})")) {
            $this->line('Cancelled. Nothing was changed.');

            return self::FAILURE;
        }

        $assignRole->handle($user, Role::SUPER_ADMIN, null, 'console');

        $this->info("{$user->email} is now a SuperAdmin.");

        return self::SUCCESS;
    }
}
