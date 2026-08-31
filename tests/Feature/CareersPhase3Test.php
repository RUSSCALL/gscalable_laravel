<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 3: the applicant dashboard and the post-login destination.
 *
 * Runs against the seeded database; anything written is cleaned up in
 * tearDown so the fixture data is left as found.
 */
class CareersPhase3Test extends TestCase
{
    private array $created = [];

    protected function tearDown(): void
    {
        JobApplication::whereIn('email', $this->created)->delete();
        User::whereIn('email', $this->created)->delete();

        parent::tearDown();
    }

    private function roleId(string $name): int
    {
        return Role::where('role_name', $name)->orderBy('id')->value('id');
    }

    /** A verified applicant who already has applications in the seed data. */
    private function applicantWithApplications(): User
    {
        $userId = JobApplication::whereNotNull('user_id')
            ->selectRaw('user_id, count(*) c')
            ->groupBy('user_id')
            ->orderByDesc('c')
            ->value('user_id');

        $user = User::find($userId);
        $this->assertNotNull($user, 'Expected a seeded applicant with applications.');

        return $user;
    }

    private function makeUser(string $tag, ?int $roleId = null, bool $verified = true): User
    {
        $email = 'p3-' . $tag . '-' . bin2hex(random_bytes(4)) . '@example.test';
        $this->created[] = $email;

        // role_id and email_verified_at are not fillable, so they are assigned
        // directly rather than passed to create().
        $user = new User();
        $user->name = 'Test Person';
        $user->email = $email;
        $user->password = Hash::make('a-sufficiently-long-password');
        $user->role_id = $roleId ?? $this->roleId('job_applicant');
        $user->email_verified_at = $verified ? now() : null;
        $user->save();

        return $user;
    }

    // ------------------------------------------------------------- access

    public function test_a_guest_is_sent_to_login(): void
    {
        $this->get(route('applicant.dashboard'))->assertRedirect(route('login'));
    }

    public function test_an_admin_cannot_open_the_applicant_dashboard(): void
    {
        $admin = $this->makeUser('admin', $this->roleId('Admin'));

        $this->actingAs($admin)
            ->get(route('applicant.dashboard'))
            ->assertForbidden();
    }

    public function test_an_unverified_applicant_is_sent_to_verify_their_email(): void
    {
        $user = $this->makeUser('unverified', null, false);

        $this->actingAs($user)
            ->get(route('applicant.dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_a_verified_applicant_can_open_their_dashboard(): void
    {
        $this->actingAs($this->applicantWithApplications())
            ->get(route('applicant.dashboard'))
            ->assertOk()
            ->assertSee('Your applications');
    }

    // -------------------------------------------------------------- content

    public function test_the_dashboard_lists_the_applicants_own_applications(): void
    {
        $user = $this->applicantWithApplications();
        $applications = JobApplication::where('user_id', $user->id)->get();

        $response = $this->actingAs($user)->get(route('applicant.dashboard'))->assertOk();

        foreach ($applications as $application) {
            $response->assertSee($application->reference);
        }
    }

    public function test_the_dashboard_never_shows_another_applicants_data(): void
    {
        $user = $this->applicantWithApplications();

        // A reference belonging to somebody else must not appear.
        $foreign = JobApplication::where('user_id', '!=', $user->id)
            ->orWhereNull('user_id')
            ->first();

        $this->assertNotNull($foreign, 'Expected an application owned by someone else.');

        $this->actingAs($user)
            ->get(route('applicant.dashboard'))
            ->assertOk()
            ->assertDontSee($foreign->reference);
    }

    public function test_an_applicant_with_no_applications_sees_the_empty_state(): void
    {
        $user = $this->makeUser('empty');

        $this->actingAs($user)
            ->get(route('applicant.dashboard'))
            ->assertOk()
            ->assertSee("haven't applied to anything yet", false)
            ->assertViewHas('totalCount', 0);
    }

    public function test_settled_applications_are_separated_from_live_ones(): void
    {
        $user = $this->applicantWithApplications();

        $response = $this->actingAs($user)->get(route('applicant.dashboard'))->assertOk();

        $active = $response->viewData('activeApplications');
        $closed = $response->viewData('closedApplications');

        // Nothing may appear in both groups, and every row lands in one.
        $this->assertSame(
            $response->viewData('totalCount'),
            $active->count() + $closed->count()
        );

        foreach ($closed as $application) {
            $this->assertSame('closed', $application->status_tone);
        }
        foreach ($active as $application) {
            $this->assertNotSame('closed', $application->status_tone);
        }
    }

    public function test_every_stored_status_renders_a_human_label_and_tone(): void
    {
        $statuses = JobApplication::select('status')->distinct()->pluck('status');
        $this->assertGreaterThan(1, $statuses->count(), 'Expected varied statuses in the seed data.');

        foreach ($statuses as $status) {
            $application = new JobApplication(['status' => $status]);
            $application->status = $status;

            // No raw column values ("under_review") should reach the applicant.
            $this->assertNotSame($status, $application->status_label, "Status {$status} has no label.");
            $this->assertStringNotContainsString('_', $application->status_label);
            $this->assertContains($application->status_tone, ['neutral', 'active', 'positive', 'closed']);
        }
    }

    // ------------------------------------------------------------ back link

    public function test_the_job_page_returns_to_the_dashboard_when_opened_from_it(): void
    {
        $user = $this->applicantWithApplications();
        $job = JobApplication::where('user_id', $user->id)->first()->jobPosting;

        $this->actingAs($user)
            ->get(route('careers.show', $job->slug), ['referer' => route('applicant.dashboard')])
            ->assertOk()
            ->assertSee('Your applications')
            ->assertSee(route('applicant.dashboard'), false);
    }

    public function test_the_job_page_returns_to_the_board_when_opened_from_it(): void
    {
        $job = JobPosting::active()->first();

        $this->get(route('careers.show', $job->slug), ['referer' => route('careers')])
            ->assertOk()
            ->assertSee('All open roles');
    }

    public function test_an_offsite_referrer_cannot_redirect_the_back_link(): void
    {
        $job = JobPosting::active()->first();

        $this->get(route('careers.show', $job->slug), ['referer' => 'https://evil.example/phish'])
            ->assertOk()
            ->assertSee('All open roles')
            ->assertDontSee('evil.example', false);
    }

    // ------------------------------------------------------- post-login route

    public function test_an_applicant_lands_on_the_dashboard_after_signing_in(): void
    {
        $this->actingAs($this->applicantWithApplications())
            ->get(route('auth.redirect'))
            ->assertRedirect(route('applicant.dashboard'));
    }

    public function test_an_admin_still_lands_on_the_admin_dashboard(): void
    {
        $admin = $this->makeUser('adminredirect', $this->roleId('Admin'));

        $this->actingAs($admin)
            ->get(route('auth.redirect'))
            ->assertRedirect(route('AdminDashboard'));
    }

    public function test_a_user_matching_no_role_gets_a_real_page_not_a_blank_one(): void
    {
        // Previously this returned null and rendered an empty response.
        $orphan = $this->makeUser('orphan', 99999);

        $this->actingAs($orphan)
            ->get(route('auth.redirect'))
            ->assertRedirect(route('home'));
    }
}
