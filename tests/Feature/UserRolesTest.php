<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RoleChange;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * SuperAdmin roles page and the user:make-superadmin command. Runs against the
 * seeded database (MyISAM, no transactions); tearDown removes every user and
 * role change written here.
 */
class UserRolesTest extends TestCase
{
    private array $emails = [];

    protected function tearDown(): void
    {
        RoleChange::whereIn('user_email', $this->emails)->delete();
        User::whereIn('email', $this->emails)->delete();

        parent::tearDown();
    }

    private function makeUser(string $role, bool $verified = true, ?string $name = null): User
    {
        $email = 'roles-' . bin2hex(random_bytes(4)) . '@example.test';
        $this->emails[] = $email;

        $user = new User();
        $user->name = $name ?? 'Roles Test ' . $role;
        $user->email = $email;
        $user->password = Hash::make('a-sufficiently-long-password');
        $user->role_id = Role::idFor($role);
        $user->email_verified_at = $verified ? now() : null;
        $user->save();

        return $user;
    }

    private function setRole(User $actor, User $target, string $role)
    {
        return $this->actingAs($actor)
            ->from(route('admin.roles'))
            ->put(route('admin.roles.update', $target), ['role' => $role]);
    }

    // ------------------------------------------------------------- access

    public function test_only_superadmins_can_open_the_roles_page(): void
    {
        $this->get(route('admin.roles'))->assertRedirect(route('login'));

        $this->actingAs($this->makeUser(Role::APPLICANT))->get(route('admin.roles'))->assertForbidden();
        $this->actingAs($this->makeUser(Role::ADMIN))->get(route('admin.roles'))->assertForbidden();

        $this->actingAs($this->makeUser(Role::SUPER_ADMIN))
            ->get(route('admin.roles'))
            ->assertOk()
            ->assertSee('Recent role changes');
    }

    public function test_only_superadmins_see_the_roles_link(): void
    {
        $this->actingAs($this->makeUser(Role::ADMIN))
            ->get(route('AdminDashboard'))
            ->assertOk()
            ->assertDontSee(route('admin.roles'), false);

        $this->actingAs($this->makeUser(Role::SUPER_ADMIN))
            ->get(route('AdminDashboard'))
            ->assertOk()
            ->assertSee(route('admin.roles'), false);
    }

    public function test_admins_cannot_change_roles(): void
    {
        $target = $this->makeUser(Role::APPLICANT);

        $this->setRole($this->makeUser(Role::ADMIN), $target, Role::ADMIN)->assertForbidden();
        $this->assertSame(Role::idFor(Role::APPLICANT), $target->refresh()->role_id);
    }

    // ------------------------------------------------------------- granting and removing

    public function test_superadmin_can_make_a_user_admin_and_take_it_away(): void
    {
        $super = $this->makeUser(Role::SUPER_ADMIN);
        $target = $this->makeUser(Role::APPLICANT);

        $this->actingAs($target)->get(route('AdminDashboard'))->assertForbidden();

        $this->setRole($super, $target, Role::ADMIN)
            ->assertRedirect(route('admin.roles'))
            ->assertSessionHas('success');
        $this->assertSame(Role::idFor(Role::ADMIN), $target->refresh()->role_id);

        // Takes effect on their very next request.
        $this->actingAs($target)->get(route('AdminDashboard'))->assertOk();

        $this->setRole($super, $target, Role::APPLICANT)->assertSessionHas('success');
        $this->assertSame(Role::idFor(Role::APPLICANT), $target->refresh()->role_id);
        $this->actingAs($target)->get(route('AdminDashboard'))->assertForbidden();

        $changes = RoleChange::where('user_email', $target->email)->orderBy('id')->get();
        $this->assertCount(2, $changes);
        $this->assertSame([Role::APPLICANT, Role::ADMIN], [$changes[0]->from_role, $changes[0]->to_role]);
        $this->assertSame([Role::ADMIN, Role::APPLICANT], [$changes[1]->from_role, $changes[1]->to_role]);
        $this->assertSame($super->id, $changes[0]->changed_by);
        $this->assertSame($super->email, $changes[0]->changed_by_email);
        $this->assertSame('web', $changes[0]->source);

        $this->actingAs($super)->get(route('admin.roles'))->assertSee($target->email);
    }

    public function test_unverified_users_cannot_be_made_admin(): void
    {
        $target = $this->makeUser(Role::APPLICANT, verified: false);

        $this->setRole($this->makeUser(Role::SUPER_ADMIN), $target, Role::ADMIN)->assertSessionHas('error');

        $this->assertSame(Role::idFor(Role::APPLICANT), $target->refresh()->role_id);
        $this->assertFalse(RoleChange::where('user_email', $target->email)->exists());
    }

    public function test_superadmin_cannot_be_granted_from_the_page(): void
    {
        $target = $this->makeUser(Role::ADMIN);

        $this->setRole($this->makeUser(Role::SUPER_ADMIN), $target, Role::SUPER_ADMIN)->assertSessionHasErrors('role');

        $this->assertSame(Role::idFor(Role::ADMIN), $target->refresh()->role_id);
    }

    public function test_superadmins_cannot_change_themselves_or_other_superadmins(): void
    {
        $super = $this->makeUser(Role::SUPER_ADMIN);
        $other = $this->makeUser(Role::SUPER_ADMIN);

        $this->setRole($super, $super, Role::APPLICANT)->assertForbidden();
        $this->setRole($super, $other, Role::APPLICANT)->assertForbidden();

        $this->assertSame(Role::idFor(Role::SUPER_ADMIN), $super->refresh()->role_id);
        $this->assertSame(Role::idFor(Role::SUPER_ADMIN), $other->refresh()->role_id);
    }

    public function test_the_page_only_offers_allowed_actions(): void
    {
        $super = $this->makeUser(Role::SUPER_ADMIN, name: 'Zz Roles Self');
        $this->makeUser(Role::SUPER_ADMIN, name: 'Zz Roles Other Super');
        $this->makeUser(Role::ADMIN, name: 'Zz Roles Admin');
        $this->makeUser(Role::APPLICANT, name: 'Zz Roles Verified');
        $this->makeUser(Role::APPLICANT, verified: false, name: 'Zz Roles Unverified');

        $html = $this->actingAs($super)->get(route('admin.roles', ['q' => 'Zz Roles']))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'This is you'));
        $this->assertSame(1, substr_count($html, 'Managed on the server'));
        // One demote form (the admin), one promote form (the verified applicant), one disabled button.
        $this->assertSame(1, substr_count($html, 'name="role" value="' . Role::APPLICANT . '"'));
        $this->assertSame(1, substr_count($html, 'name="role" value="' . Role::ADMIN . '"'));
        $this->assertSame(1, substr_count($html, 'title="They must verify their email first"'));
    }

    public function test_search_and_role_filter_narrow_the_list(): void
    {
        $super = $this->makeUser(Role::SUPER_ADMIN);
        $admin = $this->makeUser(Role::ADMIN, name: 'Zz Filter Admin');
        $applicant = $this->makeUser(Role::APPLICANT, name: 'Zz Filter Applicant');

        $this->actingAs($super)->get(route('admin.roles', ['q' => 'Zz Filter', 'role' => 'admin']))
            ->assertSee($admin->email)
            ->assertDontSee($applicant->email);

        $this->actingAs($super)->get(route('admin.roles', ['q' => $applicant->email]))
            ->assertSee($applicant->email)
            ->assertDontSee($admin->email);
    }

    // ------------------------------------------------------------- console command

    public function test_command_makes_a_user_superadmin_after_confirmation(): void
    {
        $user = $this->makeUser(Role::ADMIN, name: 'Console Person');

        $this->artisan('user:make-superadmin', ['email' => strtoupper($user->email)])
            ->expectsConfirmation("Make Console Person <{$user->email}> a SuperAdmin? (currently: Admin)", 'yes')
            ->expectsOutput("{$user->email} is now a SuperAdmin.")
            ->assertSuccessful();

        $this->assertSame(Role::idFor(Role::SUPER_ADMIN), $user->refresh()->role_id);

        $change = RoleChange::where('user_email', $user->email)->sole();
        $this->assertSame('console', $change->source);
        $this->assertNull($change->changed_by);
        $this->assertSame([Role::ADMIN, Role::SUPER_ADMIN], [$change->from_role, $change->to_role]);
    }

    public function test_command_changes_nothing_when_declined_or_unknown(): void
    {
        $user = $this->makeUser(Role::APPLICANT, name: 'Console Decline');

        $this->artisan('user:make-superadmin', ['email' => $user->email])
            ->expectsConfirmation("Make Console Decline <{$user->email}> a SuperAdmin? (currently: job_applicant)", 'no')
            ->assertFailed();
        $this->assertSame(Role::idFor(Role::APPLICANT), $user->refresh()->role_id);
        $this->assertFalse(RoleChange::where('user_email', $user->email)->exists());

        $this->artisan('user:make-superadmin', ['email' => 'nobody-' . bin2hex(random_bytes(4)) . '@example.test'])
            ->assertFailed();
    }

    public function test_command_leaves_an_existing_superadmin_alone(): void
    {
        $user = $this->makeUser(Role::SUPER_ADMIN);

        $this->artisan('user:make-superadmin', ['email' => $user->email])
            ->expectsOutput("{$user->email} is already a SuperAdmin.")
            ->assertSuccessful();

        $this->assertFalse(RoleChange::where('user_email', $user->email)->exists());
    }
}
