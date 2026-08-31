<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

/**
 * Phase 1 of the careers rebuild: board filtering, job detail SEO, legacy
 * redirects and the application form's validation surface.
 *
 * These run against the existing seeded database (no RefreshDatabase) because
 * the environment's data is the fixture — the suite only reads, except for the
 * one submission test, which cleans up after itself.
 */
class CareersPhase1Test extends TestCase
{
    private function openJob(): JobPosting
    {
        $job = JobPosting::active()->first();
        $this->assertNotNull($job, 'Expected at least one active job posting in the database.');

        return $job;
    }

    public function test_board_renders_with_filter_controls(): void
    {
        $response = $this->get(route('careers'));

        $response->assertOk()
            ->assertSee('Open Positions')
            ->assertSee('name="q"', false)
            ->assertSee('name="category"', false)
            ->assertSee('name="employment_type"', false)
            ->assertSee('name="experience_level"', false)
            ->assertSee('name="salary_min"', false)
            ->assertSee('name="sort"', false);

        // A GET form must not carry a CSRF token into the URL. Scoped to the
        // filter form itself -- since Phase 4 the page also carries the job
        // alert form, which is a POST and correctly does have a token.
        $html = $response->getContent();
        $filterForm = substr(
            $html,
            strpos($html, 'careers-filter-bar'),
            strpos($html, '</form>', strpos($html, 'careers-filter-bar')) - strpos($html, 'careers-filter-bar')
        );

        $this->assertStringNotContainsString('name="_token"', $filterForm);
    }

    public function test_board_filters_narrow_the_result_set(): void
    {
        $unfiltered = $this->get(route('careers'))->viewData('jobPostings')->total();
        $filtered = $this->get(route('careers', ['experience_level' => 'Senior']))
            ->viewData('jobPostings')->total();

        $this->assertLessThan($unfiltered, $filtered);
        $this->assertGreaterThan(0, $filtered);
    }

    public function test_board_exposes_removable_filter_chips(): void
    {
        $response = $this->get(route('careers', ['q' => 'engineer', 'employment_type' => 'Full-time']));

        $filters = $response->viewData('filters');
        $this->assertCount(2, $filters);

        // Each chip drops only its own parameter.
        $this->assertStringContainsString('employment_type=Full-time', $filters[0]['remove_url']);
        $this->assertStringNotContainsString('q=engineer', $filters[0]['remove_url']);
        $this->assertStringContainsString('q=engineer', $filters[1]['remove_url']);
        $this->assertStringNotContainsString('employment_type', $filters[1]['remove_url']);
    }

    public function test_featured_strip_only_shows_on_an_unfiltered_board(): void
    {
        $this->get(route('careers'))->assertViewHas('hasFilters', false);
        $this->get(route('careers', ['q' => 'engineer']))->assertViewHas('hasFilters', true);
    }

    public function test_invalid_sort_falls_back_to_newest(): void
    {
        $this->get(route('careers', ['sort' => 'nonsense; drop table']))
            ->assertOk()
            ->assertViewHas('sort', 'newest');
    }

    public function test_pagination_preserves_the_query_string(): void
    {
        $paginator = $this->get(route('careers', ['employment_type' => 'Full-time']))
            ->viewData('jobPostings');

        $this->assertStringContainsString('employment_type=Full-time', $paginator->nextPageUrl() ?? $paginator->url(1));
    }

    public function test_job_detail_has_seo_metadata_and_valid_json_ld(): void
    {
        $job = $this->openJob();
        $html = $this->get(route('careers.show', $job->slug))->assertOk()->getContent();

        // Exactly one title tag — the layout must not also emit its default.
        $this->assertSame(1, preg_match_all('/<title>.*?<\/title>/s', $html));
        $this->assertStringContainsString('<link rel="canonical"', $html);
        $this->assertStringContainsString('property="og:type"', $html);

        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m);
        $this->assertNotEmpty($m, 'Job detail page is missing its JSON-LD block.');

        $schema = json_decode($m[1], true);
        $this->assertSame(JSON_ERROR_NONE, json_last_error(), 'JSON-LD is not valid JSON.');
        $this->assertSame('JobPosting', $schema['@type']);
        $this->assertSame($job->title, $schema['title']);
        $this->assertArrayHasKey('hiringOrganization', $schema);
        $this->assertArrayHasKey('datePosted', $schema);
        $this->assertArrayHasKey('validThrough', $schema);
    }

    public function test_job_detail_renders_plain_text_body_as_line_breaks(): void
    {
        $job = $this->openJob();
        $html = $this->get(route('careers.show', $job->slug))->getContent();

        // Seeded copy is \n-delimited plain text; without nl2br it collapses
        // into a single run-on paragraph.
        $this->assertStringContainsString('<br />', $html);
    }

    public function test_job_detail_escapes_html_in_stored_copy(): void
    {
        $job = $this->openJob();
        $original = $job->description;

        $job->forceFill(['description' => "Safe line\n<script>alert(1)</script>"])->save();

        try {
            $this->get(route('careers.show', $job->slug))
                ->assertOk()
                ->assertDontSee('<script>alert(1)</script>', false)
                ->assertSee('&lt;script&gt;', false);
        } finally {
            $job->forceFill(['description' => $original])->save();
        }
    }

    public function test_legacy_urls_redirect_permanently(): void
    {
        $job = $this->openJob();

        $this->get("/applicantjobs/{$job->slug}")
            ->assertStatus(301)
            ->assertRedirect(route('careers.show', $job->slug));

        $this->get('/applicantjobs/search')->assertStatus(301);

        $this->get("/jobs/{$job->slug}/apply")
            ->assertStatus(301)
            ->assertRedirect(route('careers.apply', $job->slug));
    }

    // The Phase 1 test asserting that applying required an account has been
    // removed: Phase 2 deliberately opened the apply routes to guests.
    // CareersPhase2Test covers the guest flow that replaced it.

    public function test_apply_form_marks_server_required_fields_as_required(): void
    {
        $job = $this->openJob();
        $html = $this->actingAs($this->applicant())
            ->get(route('careers.apply', $job->slug))
            ->assertOk()
            ->getContent();

        foreach (['phone', 'years_of_experience', 'education', 'highest_degree'] as $field) {
            $this->assertMatchesRegularExpression(
                '/id="' . $field . '"[^>]*required/',
                $html,
                "Field {$field} is required server-side but not marked required in the form."
            );
        }
    }

    public function test_submitting_an_empty_form_reports_every_missing_field(): void
    {
        $job = $this->openJob();

        $this->actingAs($this->applicant())
            ->post(route('careers.apply.store', $job->slug), [])
            ->assertSessionHasErrors([
                'first_name', 'last_name', 'email', 'phone',
                'resume_path', 'education', 'highest_degree',
                'years_of_experience', 'terms_agree',
            ]);
    }

    public function test_a_modest_expected_salary_is_accepted(): void
    {
        $job = $this->openJob();

        // The old rule was min:30000, which silently rejected valid input.
        $this->actingAs($this->applicant())
            ->post(route('careers.apply.store', $job->slug), ['expected_salary' => 500])
            ->assertSessionDoesntHaveErrors('expected_salary');
    }

    private function applicant(): User
    {
        $roleId = Role::where('role_name', 'job_applicant')->orderBy('id')->value('id');
        $user = User::where('role_id', $roleId)->whereNotNull('email_verified_at')->first();

        $this->assertNotNull($user, 'Expected a verified job_applicant user in the database.');

        return $user;
    }
}
