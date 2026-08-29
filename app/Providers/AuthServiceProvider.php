<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // role is null-safe: a user pointing at a missing/orphaned role row
        // must fail the gate, not throw.
        Gate::define('user-is-an-applicant' ,
        function ($user) {
            return $user->role?->role_name === 'job_applicant';
        });

        Gate::define('user-is-admin' ,
        function ($user) {
            return in_array($user->role?->role_name, ['Admin', 'SuperAdmin'], true);
        });

        Gate::define('user-is-superadmin' ,
        function ($user) {
            return $user->role?->role_name === 'SuperAdmin';
        });

    }
}
