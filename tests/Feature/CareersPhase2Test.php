<?php

namespace Tests\Feature;

use App\Mail\ApplicationConfirmation;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Phase 2: guest apply, the confirmation page, and the optional account claim.
 *
 * Runs against the seeded database. Every test that writes cleans up after
 * itself so the fixture data is left as found.
 */
class CareersPhase2Test extends TestCase
{
    private array $created = [];

    protected function tearDown(): void
    {
        // Remove anything a test created, even if it failed mid-way.
        JobApplication::whereIn('email', $this->created)->delete();
        User::whereIn('email', $this->created)->delete();

        parent::tearDown();
    }

    private function job(): JobPosting
    {
        $job = JobPosting::active()->first();
        $this->assertNotNull($job, 'Expected an active job posting.');

        return $job;
    }

    /** A unique address per test so runs never collide. */
    private function freshEmail(string $tag): string
    {
        $email = 'p2-' . $tag . '-' . bin2hex(random_bytes(4)) . '@example.test';
        $this->created[] = $email;

        return $email;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Casey',
            'last_name' => 'Rivera',
            'email' => $this->freshEmail('apply'),
            'phone' => '555-0142',
            'education' => 'State University',
            'highest_degree' => "Bachelor's",
            'years_of_experience' => 5,
            'terms_agree' => '1',
            // Rendered more than the minimum fill time ago.
            '_ts' => Crypt::encryptString(time() - 30),
            'resume_path' => UploadedFile::fake()->create('resume.pdf', 120, 'application/pdf'),
        ], $overrides);
    }

    // ---------------------------------------------------------------- guests

    public function test_a_guest_can_open_the_application_form(): void
    {
        $this->get(route('careers.apply', $this->job()->slug))
            ->assertOk()
            ->assertSee('Submit application', false);
    }

    public function test_a_guest_can_submit_an_application_without_an_account(): void
    {
        Mail::fake();
        Storage::fake('public');

        $job = $this->job();
        $payload = $this->payload();

        $this->post(route('careers.apply.store', $job->slug), $payload)
            ->assertRedirect(route('careers.applied', $job->slug));

        $application = JobApplication::where('email', $payload['email'])->first();

        $this->assertNotNull($application, 'Guest application was not saved.');
        $this->assertNull($application->user_id, 'Guest application must not be tied to a user.');
        $this->assertSame($job->id, $application->job_posting_id);
        Mail::assertSent(ApplicationConfirmation::class);

        JobPosting::where('id', $job->id)->decrement('applications_count');
    }

    public function test_every_application_gets_a_unique_opaque_reference(): void
    {
        Mail::fake();
        Storage::fake('public');

        $job = $this->job();
        $payload = $this->payload();

        $this->post(route('careers.apply.store', $job->slug), $payload);
        $application = JobApplication::where('email', $payload['email'])->first();

        // Opaque: not the row id, and not guessable from it.
        $this->assertMatchesRegularExpression('/^GST-[A-Z2-9]{4}-[A-Z2-9]{4}$/', $application->reference);
        $this->assertStringNotContainsString((string) $application->id, $application->reference);

        JobPosting::where('id', $job->id)->decrement('applications_count');
    }

    public function test_the_reference_cannot_be_set_from_user_input(): void
    {
        Mail::fake();
        Storage::fake('public');

        $job = $this->job();
        $payload = $this->payload(['reference' => 'GST-HACK-HACK']);

        $this->post(route('careers.apply.store', $job->slug), $payload);
        $application = JobApplication::where('email', $payload['email'])->first();

        $this->assertNotSame('GST-HACK-HACK', $application->reference);

        JobPosting::where('id', $job->id)->decrement('applications_count');
    }

    // ------------------------------------------------------------ bot checks

    public function test_a_filled_honeypot_is_rejected_without_saving(): void
    {
        Storage::fake('public');
        $job = $this->job();
        $payload = $this->payload(['website' => 'http://spam.example']);

        $this->post(route('careers.apply.store', $job->slug), $payload)
            ->assertRedirect(route('careers.show', $job->slug))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('job_applications', ['email' => $payload['email']]);
    }

    public function test_an_instant_submission_is_rejected_without_saving(): void
    {
        Storage::fake('public');
        $job = $this->job();
        $payload = $this->payload(['_ts' => Crypt::encryptString(time())]);

        $this->post(route('careers.apply.store', $job->slug), $payload)
            ->assertRedirect(route('careers.show', $job->slug));

        $this->assertDatabaseMissing('job_applications', ['email' => $payload['email']]);
    }

    public function test_a_forged_timestamp_is_rejected(): void
    {
        Storage::fake('public');
        $job = $this->job();

        // A plain integer, as a bot would send when guessing the field format.
        $payload = $this->payload(['_ts' => (string) (time() - 9999)]);

        $this->post(route('careers.apply.store', $job->slug), $payload);

        $this->assertDatabaseMissing('job_applications', ['email' => $payload['email']]);
    }

    public function test_a_rejected_bot_submission_never_receives_a_reference(): void
    {
        Storage::fake('public');
        $job = $this->job();
        $payload = $this->payload(['website' => 'spam']);

        $response = $this->post(route('careers.apply.store', $job->slug), $payload);

        // The neutral message must not leak which check fired, and must not
        // carry a reference number for an application that was never saved.
        $this->assertNull(session('recent_application'));
        $response->assertSessionMissing('success');
        $this->assertStringNotContainsString('GST-', session('error') ?? '');
    }

    // ------------------------------------------------------------- duplicates

    public function test_the_same_email_cannot_apply_to_one_job_twice(): void
    {
        Mail::fake();
        Storage::fake('public');

        $job = $this->job();
        $email = $this->freshEmail('dupe');

        $this->post(route('careers.apply.store', $job->slug), $this->payload(['email' => $email]));
        $this->assertSame(1, JobApplication::where('email', $email)->count());

        $this->post(route('careers.apply.store', $job->slug), $this->payload(['email' => $email]))
            ->assertSessionHas('error');

        $this->assertSame(1, JobApplication::where('email', $email)->count(), 'A duplicate row was written.');

        JobPosting::where('id', $job->id)->decrement('applications_count');
    }

    // ---------------------------------------------------- confirmation page

    public function test_the_confirmation_page_shows_the_reference_after_applying(): void
    {
        Mail::fake();
        Storage::fake('public');

        $job = $this->job();
        $payload = $this->payload();

        $this->post(route('careers.apply.store', $job->slug), $payload);
        $reference = JobApplication::where('email', $payload['email'])->value('reference');

        $this->get(route('careers.applied', $job->slug))
            ->assertOk()
            ->assertSee($reference)
            ->assertSee('Application received');

        JobPosting::where('id', $job->id)->decrement('applications_count');
    }

    public function test_the_confirmation_page_is_not_reachable_without_applying(): void
    {
        $job = $this->job();

        // No session state — must not expose anyone else's reference.
        $this->get(route('careers.applied', $job->slug))
            ->assertRedirect(route('careers.show', $job->slug));
    }

    // -------------------------------------------------------- account claim

    public function test_claiming_creates_an_applicant_account_and_links_past_applications(): void
    {
        Mail::fake();
        Storage::fake('public');

        $job = $this->job();
        $payload = $this->payload();

        $this->post(route('careers.apply.store', $job->slug), $payload);

        $this->post(route('careers.claim'), [
            'email' => $payload['email'],
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'correct-horse-battery-staple',
        ])->assertRedirect();

        $user = User::where('email', $payload['email'])->first();
        $this->assertNotNull($user, 'Account was not created.');

        $applicantRole = Role::where('role_name', 'job_applicant')->orderBy('id')->value('id');
        $this->assertSame($applicantRole, $user->role_id, 'Account did not get the applicant role.');

        // Verification is required, so the account starts unverified.
        $this->assertNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);

        // The guest application is adopted by the new account.
        $this->assertSame(
            $user->id,
            JobApplication::where('email', $payload['email'])->value('user_id')
        );

        JobPosting::where('id', $job->id)->decrement('applications_count');
    }

    public function test_claiming_is_refused_without_a_recent_application(): void
    {
        $email = $this->freshEmail('noclaim');

        $this->post(route('careers.claim'), [
            'email' => $email,
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'correct-horse-battery-staple',
        ])->assertRedirect(route('careers'));

        $this->assertDatabaseMissing('users', ['email' => $email]);
        $this->assertGuest();
    }

    public function test_claiming_cannot_target_a_different_email_than_the_one_applied_with(): void
    {
        Mail::fake();
        Storage::fake('public');

        $job = $this->job();
        $payload = $this->payload();
        $this->post(route('careers.apply.store', $job->slug), $payload);

        $victim = $this->freshEmail('victim');

        $this->post(route('careers.claim'), [
            'email' => $victim,
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'correct-horse-battery-staple',
        ]);

        $this->assertDatabaseMissing('users', ['email' => $victim]);
        $this->assertGuest();

        JobPosting::where('id', $job->id)->decrement('applications_count');
    }

    public function test_claiming_never_mutates_an_existing_account(): void
    {
        Mail::fake();
        Storage::fake('public');

        // An existing user who applies again as a guest.
        $existing = User::create([
            'name' => 'Existing Person',
            'email' => $this->freshEmail('existing'),
            'password' => Hash::make('original-password-here'),
        ]);
        $originalHash = $existing->fresh()->password;

        $job = $this->job();
        $this->post(route('careers.apply.store', $job->slug), $this->payload(['email' => $existing->email]));

        $this->post(route('careers.claim'), [
            'email' => $existing->email,
            'password' => 'attacker-chosen-password',
            'password_confirmation' => 'attacker-chosen-password',
        ])->assertRedirect(route('login'));

        // The password must be untouched and no session opened.
        $this->assertSame($originalHash, $existing->fresh()->password);
        $this->assertGuest();

        JobPosting::where('id', $job->id)->decrement('applications_count');
    }

    public function test_the_claim_offer_is_hidden_when_the_email_already_has_an_account(): void
    {
        Mail::fake();
        Storage::fake('public');

        $existing = User::create([
            'name' => 'Known Person',
            'email' => $this->freshEmail('known'),
            'password' => Hash::make('original-password-here'),
        ]);

        $job = $this->job();
        $this->post(route('careers.apply.store', $job->slug), $this->payload(['email' => $existing->email]));

        $this->get(route('careers.applied', $job->slug))
            ->assertOk()
            ->assertSee('already have an account', false)
            ->assertDontSee('Choose a password', false);

        JobPosting::where('id', $job->id)->decrement('applications_count');
    }

    // ---------------------------------------------------------- signed in

    public function test_a_signed_in_applicant_gets_their_details_prefilled(): void
    {
        $roleId = Role::where('role_name', 'job_applicant')->orderBy('id')->value('id');
        $user = User::where('role_id', $roleId)->whereNotNull('email_verified_at')->first();

        $job = JobPosting::active()
            ->whereNotIn('id', JobApplication::where('email', $user->email)->pluck('job_posting_id'))
            ->first();

        $this->assertNotNull($job, 'Expected a job this user has not applied to.');

        $this->actingAs($user)
            ->get(route('careers.apply', $job->slug))
            ->assertOk()
            ->assertSee('value="' . e($user->email) . '"', false);
    }
}
