<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('admin.profile', ['user' => $request->user()]);
    }

    public function update(Request $request, UpdatesUserProfileInformation $updater)
    {
        $user = $request->user();
        $emailChanged = $request->input('email') !== $user->email;

        $updater->update($user, $request->only('name', 'email'));

        // A changed address must be re-verified before the admin area opens again.
        if ($emailChanged) {
            return redirect()->route('verification.notice')
                ->with('success', 'Profile saved. Check your new email address for a verification link.');
        }

        return back()->with('success', 'Profile saved.');
    }

    public function updatePassword(Request $request, UpdatesUserPasswords $updater)
    {
        $updater->update($request->user(), $request->only('current_password', 'password', 'password_confirmation'));

        return back()->with('success', 'Password changed.');
    }
}
