<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Creates the optional account offered on the application confirmation page.
 *
 * This endpoint is unauthenticated and it creates users, so every guard here
 * is load-bearing: it may only ever act on the email address of the
 * application submitted in this session, and it must never touch an account
 * that already exists.
 */
class ApplicantAccountController extends Controller
{
    // Same password policy as registration — one definition, not two.
    use PasswordValidationRules;

    public function store(Request $request)
    {
        $recent = session('recent_application');

        // No application in this session means there is nothing to claim.
        // Without this the route would be an open registration endpoint.
        if (! $recent) {
            return redirect()->route('careers')
                ->with('error', 'That link has expired. Please sign in if you already have an account.');
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'password' => $this->passwordRules(),
        ]);

        // The posted email must match the application's. Prevents this being
        // used to create an account on an address the applicant never proved
        // any connection to.
        if (strcasecmp($validated['email'], $recent['email']) !== 0) {
            return back()->with('error', 'That email does not match the application you just submitted.');
        }

        // Never attach to, or overwrite, an existing account from an
        // unauthenticated endpoint — send them to sign in instead.
        if (User::where('email', $recent['email'])->exists()) {
            return redirect()->route('login')
                ->with('error', 'You already have an account with that email. Sign in to see your application.');
        }

        // Resolve the role by name: the roles table has duplicate rows from a
        // double-run seeder, so the literal id 3 is not safe to assume.
        $roleId = Role::where('role_name', 'job_applicant')->orderBy('id')->value('id');

        $user = DB::transaction(function () use ($recent, $validated, $roleId) {
            $user = new User();
            $user->name = trim($recent['first_name'] . ' ' . $recent['last_name']);
            $user->email = $recent['email'];
            $user->password = $validated['password']; // hashed by the model cast
            $user->role_id = $roleId;                 // not fillable — set explicitly
            $user->save();

            // Adopt every prior guest application from this address so the
            // dashboard shows their full history, not just the latest one.
            JobApplication::where('email', $recent['email'])
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]);

            return $user;
        });

        Auth::login($user);

        // Fortify's verification email is sent in response to this event.
        event(new Registered($user));

        $request->session()->forget('recent_application');

        // The dashboard sits behind `verified`, so this lands on the
        // verification notice until they confirm. Phase 3 adds the dashboard.
        return redirect()->route('careers')
            ->with('success', 'Account created. Check your email to confirm your address.');
    }
}
