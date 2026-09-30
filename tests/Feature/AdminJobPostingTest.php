<?php

namespace Tests\Feature;

use App\Models\JobCategory;
use App\Models\JobLocation;
use App\Models\JobPosting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Admin job create/edit/publish flow. Runs against the seeded database (MyISAM,
 * so no transactions); every user, job, category and location written here is
 * deleted in tearDown.
 */
class AdminJobPostingTest extends TestCase
{
    private array $userIds = [];

    protected function tearDown(): void
    {
        JobPosting::whereIn('created_by', $this->userIds)->delete();
        User::whereIn('id', $this->userIds)->delete();
        JobCategory::where('name', 'like', 'ZZ Test Cat %')->delete();
        JobLocation::where('city', 'like', 'ZZTestville%')->delete();

        parent::tearDown();
    }

    private function makeUser(string $role): User
    {
        $user = new User();
        $user->name = 'Test ' . $role;
        $user->email = 'adm-' . bin2hex(random_bytes(4)) . '@example.test';
        $user->password = Hash::make('a-sufficiently-long-password');
        $user->role_id = Role::where('role_name', $role)->orderBy('id')->value('id');
        $user->email_verified_at = now();
        $user->save();
        $this->userIds[] = $user->id;

        return $user;
    }

    private function admin(): User
    {
        return $this->makeUser('Admin');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Role ' . bin2hex(random_bytes(3)),
            'employment_type' => 'Full-time',
            'experience_level' => 'Senior',
            'salary_min' => '90000',
            'salary_max' => '120000',
            'salary_period' => 'Annual',
            'application_deadline' => now()->addMonth()->toDateString(),
            'description' => "Line one.\nLine two.",
            'requirements' => 'Five years of PHP.',
            'responsibilities' => 'Build things.',
            'benefits' => '',
            // What the form sends for unticked switches (hidden input).
            'is_remote' => '0',
            'is_featured' => '0',
            'is_published' => '0',
        ], $overrides);
    }

    private function existingJob(array $attributes = []): JobPosting
    {
        $job = new JobPosting(array_merge([
            'title' => 'Existing Role',
            'slug' => 'existing-role-' . bin2hex(random_bytes(3)),
            'description' => 'd',
            'requirements' => 'r',
            'responsibilities' => 'x',
            'employment_type' => 'Full-time',
            'experience_level' => 'Senior',
            'application_deadline' => now()->addMonth()->toDateString(),
            'is_published' => true,
            'published_at' => now()->subWeek(),
        ], $attributes));
        $job->created_by = $this->admin()->id;
        $job->save();

        return $job;
    }

    // ------------------------------------------------------------- access

    public function test_guests_and_applicants_cannot_manage_jobs(): void
    {
        $this->get(route('jobs.create'))->assertRedirect(route('login'));

        $applicant = $this->makeUser('job_applicant');
        $this->actingAs($applicant)->get(route('jobs.create'))->assertForbidden();
        $this->actingAs($applicant)->post(route('jobs.store'), $this->payload())->assertForbidden();
    }

    // ------------------------------------------------------------- create

    public function test_admin_sees_the_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('jobs.create'))
            ->assertOk()
            ->assertSee('action="' . route('jobs.preview') . '"', false)
            ->assertSee('name="is_published"', false);
    }

    public function test_a_published_job_goes_live_on_the_careers_page(): void
    {
        $admin = $this->admin();
        $payload = $this->payload(['is_published' => '1']);

        $this->actingAs($admin)->post(route('jobs.store'), $payload)
            ->assertRedirect(route('jobListings'))
            ->assertSessionHasNoErrors();

        $job = JobPosting::where('title', $payload['title'])->firstOrFail();
        $this->assertTrue($job->is_published);
        $this->assertFalse($job->is_featured);
        $this->assertNotNull($job->published_at);
        $this->assertSame($admin->id, $job->created_by);

        $this->get(route('careers.show', $job->slug))->assertOk()->assertSee($payload['title']);
        $this->get(route('careers', ['q' => $payload['title']]))->assertSee($payload['title']);
    }

    public function test_unticked_publish_saves_a_hidden_draft(): void
    {
        $payload = $this->payload();

        $this->actingAs($this->admin())->post(route('jobs.store'), $payload)->assertSessionHasNoErrors();

        $job = JobPosting::where('title', $payload['title'])->firstOrFail();
        $this->assertFalse($job->is_published);
        $this->assertFalse($job->is_featured);
        $this->assertNull($job->published_at);

        $this->get(route('careers.show', $job->slug))->assertNotFound();
    }

    public function test_invalid_input_is_rejected_and_nothing_is_saved(): void
    {
        $admin = $this->admin();
        $before = JobPosting::count();

        $cases = [
            'title' => ['title' => ''],
            'employment_type' => ['employment_type' => 'Gig'],
            'experience_level' => ['experience_level' => 'Wizard'],
            'salary_max' => ['salary_min' => '100', 'salary_max' => '50'],
            'salary_period' => ['salary_period' => ''],
            'application_deadline' => ['application_deadline' => now()->subDay()->toDateString()],
            'category_id' => ['category_id' => '999999'],
        ];

        foreach ($cases as $field => $overrides) {
            $this->actingAs($admin)
                ->from(route('jobs.create'))
                ->post(route('jobs.store'), $this->payload($overrides))
                ->assertRedirect(route('jobs.create'))
                ->assertSessionHasErrors($field);
        }

        $this->assertSame($before, JobPosting::count());
    }

    public function test_a_salary_ceiling_alone_is_accepted(): void
    {
        $payload = $this->payload(['salary_min' => '', 'salary_max' => '70000']);

        $this->actingAs($this->admin())->post(route('jobs.store'), $payload)->assertSessionHasNoErrors();

        $this->assertNull(JobPosting::where('title', $payload['title'])->value('salary_min'));
    }

    public function test_slugs_are_unique_and_fit_the_column(): void
    {
        $admin = $this->admin();
        $title = trim(str_repeat('Very Long Senior Platform Engineering Title ', 5));

        $this->actingAs($admin)->post(route('jobs.store'), $this->payload(['title' => $title]))->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('jobs.store'), $this->payload(['title' => $title]))->assertSessionHasNoErrors();

        $slugs = JobPosting::where('title', $title)->whereIn('created_by', $this->userIds)->pluck('slug');
        $this->assertCount(2, $slugs);
        $this->assertCount(2, $slugs->unique());
        $slugs->each(fn ($slug) => $this->assertLessThanOrEqual(100, strlen($slug)));
    }

    public function test_remote_without_a_location_uses_the_remote_location(): void
    {
        $payload = $this->payload(['is_remote' => '1']);

        $this->actingAs($this->admin())->post(route('jobs.store'), $payload)->assertSessionHasNoErrors();

        $this->assertTrue((bool) JobPosting::where('title', $payload['title'])->firstOrFail()->location->is_remote);
    }

    // ------------------------------------------------------------- edit

    public function test_admin_sees_the_edit_form_prefilled(): void
    {
        $job = $this->existingJob(['title' => 'Prefill Check Role']);

        $this->actingAs($this->admin())
            ->get(route('jobs.edit', $job->id))
            ->assertOk()
            ->assertSee('value="Prefill Check Role"', false)
            ->assertSee('value="' . $job->application_deadline->format('Y-m-d') . '"', false);
    }

    public function test_updating_changes_the_listing_and_keeps_the_original_publish_date(): void
    {
        $job = $this->existingJob();
        $publishedAt = $job->published_at;

        $this->actingAs($this->admin())
            ->put(route('jobs.update', $job->id), $this->payload([
                'title' => 'Renamed Role',
                'is_published' => '1',
                'is_featured' => '1',
            ]))
            ->assertRedirect(route('jobListings'))
            ->assertSessionHasNoErrors();

        $job->refresh();
        $this->assertSame('Renamed Role', $job->title);
        $this->assertStringStartsWith('renamed-role', $job->slug);
        $this->assertTrue($job->is_featured);
        $this->assertEquals($publishedAt, $job->published_at);
    }

    public function test_unpublishing_via_the_form_takes_the_job_down(): void
    {
        $job = $this->existingJob();

        $this->actingAs($this->admin())
            ->put(route('jobs.update', $job->id), $this->payload(['title' => $job->title]))
            ->assertSessionHasNoErrors();

        $this->assertFalse($job->refresh()->is_published);
        $this->get(route('careers.show', $job->slug))->assertNotFound();
    }

    public function test_a_legacy_option_value_survives_an_edit(): void
    {
        $job = $this->existingJob(['experience_level' => 'Principal']);

        $this->actingAs($this->admin())
            ->put(route('jobs.update', $job->id), $this->payload(['title' => $job->title, 'experience_level' => 'Principal']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Principal', $job->refresh()->experience_level);
    }

    public function test_an_expired_draft_can_be_edited_but_not_published(): void
    {
        $admin = $this->admin();
        $past = now()->subWeek()->toDateString();
        $job = $this->existingJob(['is_published' => false, 'published_at' => null, 'application_deadline' => $past]);

        $this->actingAs($admin)
            ->put(route('jobs.update', $job->id), $this->payload(['title' => $job->title, 'application_deadline' => $past]))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->put(route('jobs.update', $job->id), $this->payload(['title' => $job->title, 'application_deadline' => $past, 'is_published' => '1']))
            ->assertSessionHasErrors('application_deadline');

        $this->actingAs($admin)->put(route('jobs.publish', $job->id))->assertSessionHas('error');
        $this->assertFalse($job->refresh()->is_published);
    }

    // ------------------------------------------------------------- other pages

    public function test_preview_and_listing_pages_render(): void
    {
        $admin = $this->admin();
        $job = $this->existingJob(['is_published' => false, 'published_at' => null]);

        $this->actingAs($admin)->get(route('jobs.show', $job->id))->assertOk()->assertSee('Draft');
        $this->actingAs($admin)->get(route('jobListings'))->assertOk()->assertSee(route('jobs.create'), false);
        $this->actingAs($admin)->get(route('jobListings', ['sort' => 'nope;drop', 'direction' => 'sideways']))->assertOk();
    }

    public function test_a_job_stays_open_through_its_deadline_day(): void
    {
        $job = $this->existingJob(['application_deadline' => today()->toDateString()]);

        $this->assertTrue(JobPosting::active()->whereKey($job->id)->exists());
    }

    // ------------------------------------------------------------- preview

    public function test_preview_renders_the_public_page_without_saving(): void
    {
        $before = JobPosting::count();
        $payload = $this->payload(['is_published' => '1']);

        $this->actingAs($this->admin())
            ->post(route('jobs.preview'), $payload)
            ->assertOk()
            ->assertViewIs('job-posting')
            ->assertSee($payload['title'])
            ->assertSee('I like how it looks', false)
            ->assertSee('action="' . route('jobs.store') . '"', false)
            ->assertSee('name="title" value="' . $payload['title'] . '"', false)
            ->assertSee('noindex', false)
            ->assertDontSee('application/ld+json', false);

        $this->assertSame($before, JobPosting::count());
    }

    public function test_previewing_an_edit_does_not_change_the_saved_job(): void
    {
        $job = $this->existingJob(['title' => 'Before Preview']);

        $this->actingAs($this->admin())
            ->post(route('jobs.preview.existing', $job->id), $this->payload(['title' => 'After Preview', 'is_published' => '1']))
            ->assertOk()
            ->assertSee('After Preview')
            ->assertSee('action="' . route('jobs.update', $job->id) . '"', false)
            ->assertSee('name="_method" value="PUT"', false);

        $this->assertSame('Before Preview', $job->refresh()->title);
    }

    public function test_preview_sends_invalid_input_back_to_the_form(): void
    {
        $this->actingAs($this->admin())
            ->from(route('jobs.create'))
            ->post(route('jobs.preview'), $this->payload(['title' => '']))
            ->assertRedirect(route('jobs.create'))
            ->assertSessionHasErrors('title');
    }

    public function test_keep_editing_returns_to_the_form_with_everything_filled_in(): void
    {
        $admin = $this->admin();
        $before = JobPosting::count();
        $payload = $this->payload(['intent' => 'edit']);

        $this->actingAs($admin)->post(route('jobs.store'), $payload)
            ->assertRedirect(route('jobs.create'))
            ->assertSessionHasInput('title', $payload['title']);
        $this->assertSame($before, JobPosting::count());

        $this->actingAs($admin)->get(route('jobs.create'))->assertSee('value="' . $payload['title'] . '"', false);

        $job = $this->existingJob(['title' => 'Unchanged Title']);
        $this->actingAs($admin)
            ->put(route('jobs.update', $job->id), $this->payload(['title' => 'Edited Title', 'intent' => 'edit']))
            ->assertRedirect(route('jobs.edit', $job->id));
        $this->assertSame('Unchanged Title', $job->refresh()->title);
    }

    // ------------------------------------------------------------- pick or add

    public function test_a_new_category_is_created_on_save_only_and_then_reused(): void
    {
        $admin = $this->admin();
        $name = 'ZZ Test Cat ' . bin2hex(random_bytes(3));

        $this->actingAs($admin)->post(route('jobs.preview'), $this->payload(['category_id' => 'new:' . $name]))
            ->assertOk()
            ->assertSee($name);
        $this->assertFalse(JobCategory::where('name', $name)->exists());

        $first = $this->payload(['category_id' => 'new:' . $name]);
        $this->actingAs($admin)->post(route('jobs.store'), $first)->assertSessionHasNoErrors();
        $category = JobCategory::where('name', $name)->firstOrFail();
        $this->assertSame($category->id, JobPosting::where('title', $first['title'])->value('category_id'));

        // Same name in different case reuses the category instead of duplicating it.
        $second = $this->payload(['category_id' => 'new:' . strtoupper($name)]);
        $this->actingAs($admin)->post(route('jobs.store'), $second)->assertSessionHasNoErrors();
        $this->assertSame(1, JobCategory::where('name', 'like', 'ZZ Test Cat %')->count());
        $this->assertSame($category->id, JobPosting::where('title', $second['title'])->value('category_id'));
    }

    public function test_a_new_location_is_parsed_from_city_and_country(): void
    {
        $admin = $this->admin();
        $city = 'ZZTestville' . bin2hex(random_bytes(2));
        $payload = $this->payload(['location_id' => "new:{$city}, Testland"]);

        $this->actingAs($admin)->post(route('jobs.store'), $payload)->assertSessionHasNoErrors();

        $location = JobPosting::where('title', $payload['title'])->firstOrFail()->location;
        $this->assertSame($city, $location->city);
        $this->assertSame('Testland', $location->country);
        $this->assertNull($location->state);
        $this->assertFalse((bool) $location->is_remote);

        $this->actingAs($admin)
            ->from(route('jobs.create'))
            ->post(route('jobs.store'), $this->payload(['location_id' => 'new:' . $city]))
            ->assertSessionHasErrors('location_id');
    }

    public function test_a_typed_in_option_survives_a_failed_submit(): void
    {
        $admin = $this->admin();
        $name = 'ZZ Test Cat ' . bin2hex(random_bytes(3));

        $this->actingAs($admin)
            ->from(route('jobs.create'))
            ->post(route('jobs.preview'), $this->payload(['title' => '', 'category_id' => 'new:' . $name]));

        $this->actingAs($admin)
            ->get(route('jobs.create'))
            ->assertSee('value="new:' . $name . '" selected', false);
    }

    public function test_an_inactive_category_stays_selected_and_labelled_on_its_job(): void
    {
        $category = JobCategory::create([
            'name' => 'ZZ Test Cat ' . bin2hex(random_bytes(3)),
            'slug' => 'zz-test-cat-' . bin2hex(random_bytes(3)),
            'is_active' => false,
        ]);
        $job = $this->existingJob(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->get(route('jobs.edit', $job->id))
            ->assertSee('<option value="' . $category->id . '" selected>' . $category->name . ' (inactive)</option>', false);
    }

    // ------------------------------------------------------------- rich text

    public function test_editor_html_is_sanitized_on_save_and_shown_formatted(): void
    {
        $payload = $this->payload([
            'is_published' => '1',
            'description' => '<div>Hi <strong>there</strong><script>alert(1)</script></div>'
                . '<ul><li><a href="javascript:alert(1)" onclick="x()">bad link</a></li></ul>',
            'benefits' => '<div>Remote <em>friendly</em></div>',
        ]);

        $this->actingAs($this->admin())->post(route('jobs.store'), $payload)->assertSessionHasNoErrors();

        $job = JobPosting::where('title', $payload['title'])->firstOrFail();
        $this->assertStringContainsString('<strong>there</strong>', $job->description);
        $this->assertStringNotContainsString('<script', $job->description);
        $this->assertStringNotContainsString('javascript:', $job->description);
        $this->assertStringNotContainsString('onclick', $job->description);

        $response = $this->get(route('careers.show', $job->slug))
            ->assertOk()
            ->assertSee('<strong>there</strong>', false)
            ->assertSee('<em>friendly</em>', false)
            ->assertDontSee('alert(1)', false);

        $schema = $response->viewData('jsonLd');
        $this->assertStringContainsString('<strong>there</strong>', $schema['description']);
        $this->assertStringContainsString('<p><strong>Benefits</strong></p><div>Remote <em>friendly</em></div>', $schema['description']);

        // The board's card excerpt is plain text, not markup.
        $this->get(route('careers', ['q' => $payload['title']]))
            ->assertSee('Hi there bad link')
            ->assertDontSee('&lt;strong&gt;', false);
    }

    public function test_a_blank_editor_fails_required(): void
    {
        $this->actingAs($this->admin())
            ->from(route('jobs.create'))
            ->post(route('jobs.store'), $this->payload(['description' => '<div><br></div>']))
            ->assertSessionHasErrors('description');
    }

    public function test_legacy_plain_text_loads_into_the_editor_with_line_breaks(): void
    {
        $job = $this->existingJob(['description' => "Line A\nLine B"]);

        $this->actingAs($this->admin())
            ->get(route('jobs.edit', $job->id))
            ->assertSee('&lt;div&gt;Line A&lt;br&gt;', false);
    }

    public function test_salary_period_reads_naturally(): void
    {
        $job = new JobPosting(['salary_min' => 1000, 'salary_max' => 2000, 'salary_period' => 'Monthly']);
        $this->assertSame('USD 1,000 - 2,000 per month', $job->salary_range);

        $job->salary_period = 'Annual';
        $this->assertSame('USD 1,000 - 2,000 per year', $job->salary_range);

        $job->salary_period = 'Hourly';
        $this->assertSame('USD 1,000 - 2,000 per hour', $job->salary_range);
    }
}
