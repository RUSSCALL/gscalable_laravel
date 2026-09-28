<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RegistrationEmailTest extends TestCase
{
    private const MESSAGE = 'Please enter a real email address and check the domain for typos.';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.turnstile.secret_key' => null]);
    }

    private function register(string $email)
    {
        return $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Test Applicant',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);
    }

    public function test_a_domain_that_does_not_exist_is_rejected(): void
    {
        $email = 'someone@nonexistent-domain-gst-test.invalid';

        $this->register($email)->assertSessionHasErrors(['email' => self::MESSAGE]);
        $this->assertFalse(User::where('email', $email)->exists());
    }

    public function test_a_malformed_email_is_rejected(): void
    {
        $this->register('not-an-email@')->assertSessionHasErrors(['email' => self::MESSAGE]);
        $this->assertFalse(User::where('email', 'not-an-email@')->exists());
    }
}
