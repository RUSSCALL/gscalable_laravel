<?php

namespace App\Support;

use App\Models\JobPosting;
use Carbon\Carbon;

/**
 * Builds schema.org JobPosting structured data so job pages are eligible for
 * Google Jobs rich results.
 *
 * @see https://developers.google.com/search/docs/appearance/structured-data/job-posting
 */
class JobPostingSchema
{
    /**
     * schema.org uses a fixed vocabulary for employmentType; our column is a
     * free string set by admins, so map what we know and drop what we don't.
     */
    private const EMPLOYMENT_TYPES = [
        'full-time' => 'FULL_TIME',
        'full time' => 'FULL_TIME',
        'part-time' => 'PART_TIME',
        'part time' => 'PART_TIME',
        'contract' => 'CONTRACTOR',
        'contractor' => 'CONTRACTOR',
        'temporary' => 'TEMPORARY',
        'internship' => 'INTERN',
        'intern' => 'INTERN',
        'volunteer' => 'VOLUNTEER',
        'per diem' => 'PER_DIEM',
    ];

    /**
     * schema.org unitText for a salary period.
     */
    private const SALARY_PERIODS = [
        'hourly' => 'HOUR',
        'hour' => 'HOUR',
        'daily' => 'DAY',
        'weekly' => 'WEEK',
        'monthly' => 'MONTH',
        'annual' => 'YEAR',
        'annually' => 'YEAR',
        'yearly' => 'YEAR',
        'year' => 'YEAR',
    ];

    public static function for(JobPosting $job): array
    {
        $schema = [
            '@context' => 'https://schema.org/',
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => self::description($job),
            'identifier' => [
                '@type' => 'PropertyValue',
                'name' => 'Global Scalable Technologies',
                'value' => date('Y') . '-' . $job->id,
            ],
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => 'Global Scalable Technologies',
                'sameAs' => url('/'),
                'logo' => asset('assets/img/Favicon1.jpg'),
            ],
            'url' => route('careers.show', $job->slug),
        ];

        if ($job->published_at) {
            $schema['datePosted'] = Carbon::parse($job->published_at)->toDateString();
        }

        if ($job->application_deadline) {
            $schema['validThrough'] = Carbon::parse($job->application_deadline)->endOfDay()->toIso8601String();
        }

        if ($type = self::employmentType($job->employment_type)) {
            $schema['employmentType'] = $type;
        }

        $schema += self::location($job);

        if ($salary = self::baseSalary($job)) {
            $schema['baseSalary'] = $salary;
        }

        return $schema;
    }

    /**
     * Google expects an HTML description. The stored copy is plain text with
     * newlines, so escape it and convert the breaks rather than echoing raw.
     */
    private static function description(JobPosting $job): string
    {
        $parts = array_filter([
            $job->description,
            $job->responsibilities ? '<strong>Responsibilities</strong>' . "\n" . $job->responsibilities : null,
            $job->requirements ? '<strong>Requirements</strong>' . "\n" . $job->requirements : null,
            $job->benefits ? '<strong>Benefits</strong>' . "\n" . $job->benefits : null,
        ]);

        return implode('<br><br>', array_map(function ($part) {
            // Keep the <strong> headings we just added, escape everything else.
            return preg_replace(
                '/&lt;(\/?strong)&gt;/',
                '<$1>',
                nl2br(e($part))
            );
        }, $parts));
    }

    private static function employmentType(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return self::EMPLOYMENT_TYPES[strtolower(trim($value))] ?? null;
    }

    /**
     * Remote roles use jobLocationType + applicantLocationRequirements;
     * on-site roles use a Place with a postal address.
     */
    private static function location(JobPosting $job): array
    {
        $location = $job->location;

        if (! $location) {
            return [];
        }

        if ($location->is_remote) {
            return [
                'jobLocationType' => 'TELECOMMUTE',
                'applicantLocationRequirements' => [
                    '@type' => 'Country',
                    'name' => $location->country ?: 'USA',
                ],
            ];
        }

        $address = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $location->address,
            'addressLocality' => $location->city,
            'addressRegion' => $location->state,
            'postalCode' => $location->postal_code,
            'addressCountry' => $location->country,
        ]);

        return [
            'jobLocation' => [
                '@type' => 'Place',
                'address' => $address,
            ],
        ];
    }

    private static function baseSalary(JobPosting $job): ?array
    {
        if (! $job->salary_min && ! $job->salary_max) {
            return null;
        }

        $value = array_filter([
            '@type' => 'QuantitativeValue',
            'minValue' => $job->salary_min ? (float) $job->salary_min : null,
            'maxValue' => $job->salary_max ? (float) $job->salary_max : null,
            'unitText' => self::SALARY_PERIODS[strtolower(trim((string) $job->salary_period))] ?? 'YEAR',
        ]);

        return [
            '@type' => 'MonetaryAmount',
            'currency' => $job->salary_currency ?: 'USD',
            'value' => $value,
        ];
    }
}
