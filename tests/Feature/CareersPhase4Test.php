<?php

namespace Tests\Feature;

use App\Mail\ApplicationConfirmation;
use App\Mail\JobAlertConfirmation;
use App\Models\JobAlertSubscription;
use App\Models\JobApplication;
use App\Models\JobCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Phase 4: job-alert capture, double opt-in, admin list, and the GST mail
 * template that all of it is delivered in.
 *
 * Runs against the seeded database; anything written is removed in tearDown.
 */
class CareersPhase4Test extends TestCase
{
    private array $emails = [];

    protected function tearDown(): void
    {
        JobAlertSubscription::whereIn('email', $this->emails)->delete();
        User::whereIn('email', $this->emails)->delete();

        parent::tearDown();
    }

    private function email(string $tag): string
    {
        $email = 'p4-' . $tag . '-' . bin2hex(random_bytes(4)) . '@example.test';
        $this->emails[] = $email;

        return $email;
    }

    /** A payload that passes both bot checks. */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'email' => $this->email('sub'),
            'website' => '',
            '_ts' => Crypt::encryptString(time() - 5),
        ], $overrides);
    }

    private function makeUser(string $tag, string $roleName): User
    {
        // role_id and email_verified_at are not fillable, so they are assigned
        // directly rather than passed to create().
        $user = new User();
        $user->name = 'Phase 4 Tester';
        $user->email = $this->email($tag);
        $user->password = Hash::make('a-sufficiently-long-password');
        $user->role_id = Role::where('role_name', $roleName)->orderBy('id')->value('id');
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    // ------------------------------------------------------------- the form

    public function test_the_board_renders_the_alert_form(): void
    {
        $this->get(route('careers'))
            ->assertOk()
            ->assertSee('id="job-alerts"', false)
            ->assertSee(route('careers.alerts.subscribe'), false)
            ->assertSee('name="_ts"', false);
    }

    // -------------------------------------------------------------- signup

    public function test_a_visitor_can_subscribe_and_is_sent_a_confirmation(): void
    {
        Mail::fake();
        $payload = $this->payload();

        $this->post(route('careers.alerts.subscribe'), $payload)
            ->assertRedirect()
            ->assertSessionHas('alert_success');

        $subscription = JobAlertSubscription::where('email', $payload['email'])->first();
        $this->assertNotNull($subscription);

        // Nothing is live until the emailed link is followed.
        $this->assertFalse($subscription->is_confirmed);
        $this->assertNotNull($subscription->confirmation_token);

        Mail::assertSent(JobAlertConfirmation::class, fn ($mail) => $mail->hasTo($payload['email']));
    }

    public function test_the_email_is_stored_lowercased_and_trimmed(): void
    {
        Mail::fake();
        $email = $this->email('case');

        $this->post(route('careers.alerts.subscribe'), $this->payload([
            'email' => '  ' . strtoupper($email) . '  ',
        ]))->assertRedirect();

        $this->assertDatabaseHas('job_alert_subscriptions', ['email' => $email]);
    }

    public function test_optional_filters_are_stored(): void
    {
        Mail::fake();
        $category = JobCategory::first();
        $payload = $this->payload(['category_id' => $category->id, 'keywords' => 'cloud security']);

        $this->post(route('careers.alerts.subscribe'), $payload)->assertRedirect();

        $this->assertDatabaseHas('job_alert_subscriptions', [
            'email' => $payload['email'],
            'category_id' => $category->id,
            'keywords' => 'cloud security',
        ]);
    }

    public function test_an_invalid_email_is_rejected(): void
    {
        Mail::fake();

        $this->post(route('careers.alerts.subscribe'), $this->payload(['email' => 'not-an-email']))
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    // ---------------------------------------------------------- bot defences

    public function test_a_filled_honeypot_is_silently_dropped(): void
    {
        Mail::fake();
        $payload = $this->payload(['website' => 'http://spam.example']);

        // The bot sees exactly what a human sees, so it learns nothing.
        $this->post(route('careers.alerts.subscribe'), $payload)
            ->assertRedirect()
            ->assertSessionHas('alert_success');

        $this->assertDatabaseMissing('job_alert_subscriptions', ['email' => $payload['email']]);
        Mail::assertNothingSent();
    }

    public function test_an_instant_submission_is_silently_dropped(): void
    {
        Mail::fake();
        $payload = $this->payload(['_ts' => Crypt::encryptString(time())]);

        $this->post(route('careers.alerts.subscribe'), $payload)
            ->assertRedirect()
            ->assertSessionHas('alert_success');

        $this->assertDatabaseMissing('job_alert_subscriptions', ['email' => $payload['email']]);
    }

    public function test_a_forged_timestamp_is_silently_dropped(): void
    {
        Mail::fake();
        $payload = $this->payload(['_ts' => 'not-encrypted-by-us']);

        $this->post(route('careers.alerts.subscribe'), $payload)->assertRedirect();

        $this->assertDatabaseMissing('job_alert_subscriptions', ['email' => $payload['email']]);
    }

    // ------------------------------------------------------------ duplicates

    public function test_resubscribing_while_pending_resends_rather_than_duplicating(): void
    {
        Mail::fake();
        $payload = $this->payload();

        $this->post(route('careers.alerts.subscribe'), $payload)->assertRedirect();
        $this->post(route('careers.alerts.subscribe'), $payload)->assertRedirect();

        $this->assertSame(1, JobAlertSubscription::where('email', $payload['email'])->count());
        Mail::assertSent(JobAlertConfirmation::class, 2);
    }

    public function test_resubscribing_once_confirmed_sends_nothing(): void
    {
        Mail::fake();
        $payload = $this->payload();

        $this->post(route('careers.alerts.subscribe'), $payload)->assertRedirect();
        JobAlertSubscription::where('email', $payload['email'])->first()->confirm();

        Mail::fake(); // reset the recorded sends

        // Re-sending here would let anyone mail-bomb an address via the form.
        $this->post(route('careers.alerts.subscribe'), $payload)
            ->assertRedirect()
            ->assertSessionHas('alert_success');

        Mail::assertNothingSent();
        $this->assertSame(1, JobAlertSubscription::where('email', $payload['email'])->count());
    }

    public function test_the_response_does_not_reveal_whether_an_address_is_subscribed(): void
    {
        Mail::fake();
        $payload = $this->payload();

        $first = $this->post(route('careers.alerts.subscribe'), $payload);
        JobAlertSubscription::where('email', $payload['email'])->first()->confirm();
        $second = $this->post(route('careers.alerts.subscribe'), $payload);

        $this->assertSame(
            $first->getSession()->get('alert_success'),
            $second->getSession()->get('alert_success')
        );
    }

    // ------------------------------------------------------------ confirming

    public function test_the_confirmation_link_activates_the_subscription(): void
    {
        Mail::fake();
        $payload = $this->payload();
        $this->post(route('careers.alerts.subscribe'), $payload);

        $subscription = JobAlertSubscription::where('email', $payload['email'])->first();

        $this->get(route('careers.alerts.confirm', $subscription->confirmation_token))
            ->assertRedirect(route('careers'))
            ->assertSessionHas('alert_success');

        $subscription->refresh();
        $this->assertTrue($subscription->is_confirmed);
        $this->assertNotNull($subscription->confirmed_at);

        // Burned, so the link cannot be replayed.
        $this->assertNull($subscription->confirmation_token);
    }

    public function test_an_unknown_confirmation_token_does_not_error(): void
    {
        $this->get(route('careers.alerts.confirm', str_repeat('f', 64)))
            ->assertRedirect(route('careers'))
            ->assertSessionHas('alert_status');
    }

    // --------------------------------------------------------- unsubscribing

    public function test_the_unsubscribe_link_removes_the_subscription(): void
    {
        Mail::fake();
        $payload = $this->payload();
        $this->post(route('careers.alerts.subscribe'), $payload);

        $subscription = JobAlertSubscription::where('email', $payload['email'])->first();

        $this->get(route('careers.alerts.unsubscribe', $subscription->unsubscribe_token))
            ->assertRedirect(route('careers'))
            ->assertSessionHas('alert_status');

        $this->assertDatabaseMissing('job_alert_subscriptions', ['email' => $payload['email']]);
    }

    public function test_an_unknown_unsubscribe_token_still_reports_success(): void
    {
        // Nobody should be left believing they are still on the list.
        $this->get(route('careers.alerts.unsubscribe', str_repeat('e', 64)))
            ->assertRedirect(route('careers'))
            ->assertSessionHas('alert_status');
    }

    // ----------------------------------------------------------------- admin

    public function test_a_guest_cannot_see_the_subscriber_list(): void
    {
        $this->get(route('admin.job-alerts'))->assertRedirect(route('login'));
    }

    public function test_an_applicant_cannot_see_the_subscriber_list(): void
    {
        $this->actingAs($this->makeUser('nosy', 'job_applicant'))
            ->get(route('admin.job-alerts'))
            ->assertForbidden();
    }

    public function test_an_admin_sees_subscribers(): void
    {
        Mail::fake();
        $payload = $this->payload();
        $this->post(route('careers.alerts.subscribe'), $payload);

        $this->actingAs($this->makeUser('admin', 'Admin'))
            ->get(route('admin.job-alerts'))
            ->assertOk()
            ->assertSee($payload['email']);
    }

    public function test_the_admin_list_can_be_filtered_by_status(): void
    {
        Mail::fake();
        $pending = $this->payload();
        $confirmed = $this->payload();

        $this->post(route('careers.alerts.subscribe'), $pending);
        $this->post(route('careers.alerts.subscribe'), $confirmed);
        JobAlertSubscription::where('email', $confirmed['email'])->first()->confirm();

        $this->actingAs($this->makeUser('filteradmin', 'Admin'))
            ->get(route('admin.job-alerts', ['status' => 'confirmed']))
            ->assertOk()
            ->assertSee($confirmed['email'])
            ->assertDontSee($pending['email']);
    }

    public function test_the_csv_export_downloads_the_rows(): void
    {
        Mail::fake();
        $payload = $this->payload();
        $this->post(route('careers.alerts.subscribe'), $payload);

        $response = $this->actingAs($this->makeUser('exportadmin', 'Admin'))
            ->get(route('admin.job-alerts', ['export' => 'csv']))
            ->assertOk();

        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Email', $csv);
        $this->assertStringContainsString($payload['email'], $csv);
    }

    // ------------------------------------------------------------ mail brand

    public function test_the_mail_template_carries_gst_branding(): void
    {
        $subscription = new JobAlertSubscription(['email' => 'brand@example.test']);
        $subscription->confirmation_token = str_repeat('a', 64);
        $subscription->unsubscribe_token = str_repeat('b', 64);

        $html = (new JobAlertConfirmation($subscription))->render();

        // The logo previously never rendered: the stock header only showed it
        // when the slot was literally the string "Laravel".
        $this->assertStringContainsString('GST-logo-white.png', $html);
        $this->assertStringContainsString('#0F1B22', $html); // navy header band
        $this->assertStringContainsString('#F2861D', $html); // orange button
        $this->assertStringNotContainsString('Laravel', $html);
        $this->assertStringContainsString('Global Scalable Technologies', $html);
    }

    public function test_the_application_confirmation_uses_the_same_branding(): void
    {
        $application = JobApplication::whereNotNull('reference')->latest('id')->first();
        $this->assertNotNull($application, 'Expected a seeded application.');

        $html = (new ApplicationConfirmation($application, $application->jobPosting))->render();

        $this->assertStringContainsString('GST-logo-white.png', $html);
        $this->assertStringContainsString('#0F1B22', $html);
        $this->assertStringNotContainsString('Laravel', $html);
    }

    public function test_the_confirmation_email_links_to_the_confirm_route(): void
    {
        $subscription = new JobAlertSubscription(['email' => 'link@example.test']);
        $subscription->confirmation_token = str_repeat('c', 64);
        $subscription->unsubscribe_token = str_repeat('d', 64);

        $html = (new JobAlertConfirmation($subscription))->render();

        $this->assertStringContainsString(
            route('careers.alerts.confirm', str_repeat('c', 64)),
            $html
        );
    }
}
