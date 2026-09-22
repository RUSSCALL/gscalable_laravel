<?php

namespace App\Actions\Fortify;

use App\Models\Role;
use App\Models\User;
use App\Support\EnsuresMailIsConfigured;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, EnsuresMailIsConfigured;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        if (! $this->mailIsConfigured()) {
            report(new \RuntimeException("Registration blocked: mail is not configured (mailer: {$this->currentMailer()})."));

            throw ValidationException::withMessages([
                'email' => __('Registration is temporarily unavailable. Please try again later or contact support.'),
            ]);
        }

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255' , 'min:2'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // Set explicitly: Eloquent does not read the column's DB default back, leaving a null role on the logged-in instance.
        $user->role_id = Role::where('role_name', 'job_applicant')->orderBy('id')->value('id');
        $user->save();

        return $user;
    }
}
