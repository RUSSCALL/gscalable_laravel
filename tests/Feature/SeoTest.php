<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use Tests\TestCase;

/**
 * Search-result signals: per-page titles, canonicals, the homepage site-name
 * schema and the sitemap. Reads the seeded database like the careers suites.
 */
class SeoTest extends TestCase
{
    private const PUBLIC_PAGES = ['home', 'contract_vehicles', 'careers', 'privacy', 'terms'];

    public function test_public_pages_have_their_own_title_description_and_one_canonical(): void
    {
        foreach (self::PUBLIC_PAGES as $page) {
            $html = $this->get(route($page))->assertOk()->getContent();

            $this->assertSame(1, preg_match_all('/<title>.*?<\/title>/s', $html), "{$page}: expected one title");
            $this->assertStringNotContainsString('<title>Global Scalable Technologies</title>', $html, "{$page}: still uses the default title");
            $this->assertStringContainsString('<meta name="description"', $html, "{$page}: missing description");
            $this->assertSame(1, substr_count($html, 'rel="canonical"'), "{$page}: expected one canonical");
            $this->assertStringNotContainsString('240-319-8823', $html, "{$page}: old phone number");
        }
    }

    public function test_job_page_has_one_canonical_pointing_at_itself(): void
    {
        $job = JobPosting::active()->firstOrFail();
        $url = route('careers.show', $job->slug);
        $html = $this->get($url)->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'rel="canonical"'));
        $this->assertStringContainsString('<link rel="canonical" href="' . $url . '">', $html);
    }

    public function test_homepage_declares_site_name_and_organization(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m);
        $this->assertNotEmpty($m, 'Homepage is missing its JSON-LD block.');

        $schema = json_decode($m[1], true);
        $this->assertSame(JSON_ERROR_NONE, json_last_error(), 'JSON-LD is not valid JSON.');

        $types = collect($schema['@graph'])->keyBy('@type');
        $this->assertSame('Global Scalable Technologies', $types['WebSite']['name']);
        $this->assertSame('GST', $types['WebSite']['alternateName']);
        $this->assertSame(url('/'), $types['WebSite']['url']);
        $this->assertSame('+1-202-819-5975', $types['Organization']['telephone']);
    }

    public function test_sitemap_lists_public_pages_and_only_open_jobs(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringStartsWith('application/xml', $response->headers->get('Content-Type'));

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml, 'Sitemap is not valid XML.');

        $locs = collect(iterator_to_array($xml->url, false))->map(fn ($url) => (string) $url->loc);

        foreach (self::PUBLIC_PAGES as $page) {
            $this->assertContains(route($page), $locs);
        }

        $openJobs = JobPosting::active()->pluck('slug');
        $this->assertCount(count(self::PUBLIC_PAGES) + $openJobs->count(), $locs);
        foreach ($openJobs as $slug) {
            $this->assertContains(route('careers.show', $slug), $locs);
        }
    }
}
