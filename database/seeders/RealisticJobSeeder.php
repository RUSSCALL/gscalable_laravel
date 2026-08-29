<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds a cohesive set of GovCon (government contracting) job postings and
 * matching applications.
 *
 * Every application is tied to a real, published job posting, and the
 * applicant's background (skills, current role, degree, salary expectation,
 * years of experience) is consistent with the job they applied to.
 *
 * Categories referenced (from JobCategorySeeder):
 *   1 Software Development  2 Design  3 Marketing  4 Sales
 *   5 Customer Support      6 Human Resources      7 Finance
 * Locations referenced (from JobLocationSeeder):
 *   1 New York NY  2 San Francisco CA  3 Austin TX  4 London UK  5 Remote
 */
class RealisticJobSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first()
            ?? User::orderBy('id')->first();

        // Applicant-role users that already exist, so a few applications can be
        // linked to registered accounts. Everyone else applies as a guest.
        $registeredApplicants = User::where('email', '!=', 'admin@example.com')
            ->pluck('email', 'id')
            ->toArray();

        DB::transaction(function () use ($admin, $registeredApplicants) {
            // --- wipe the old auto-generated data -----------------------------
            // job_applications has an FK to job_postings with cascadeOnDelete,
            // so deleting the postings clears their applications too. We delete
            // applications first anyway to be explicit.
            DB::table('job_applications')->delete();
            DB::table('job_postings')->delete();

            $now = Carbon::now();

            foreach ($this->jobs() as $job) {
                $postedAt   = (clone $now)->subDays($job['posted_days_ago']);
                $deadline   = (clone $now)->addDays($job['deadline_in_days']);
                $isPublished = $job['is_published'];

                $jobId = DB::table('job_postings')->insertGetId([
                    'title'                => $job['title'],
                    'slug'                 => Str::slug($job['title']) . '-' . strtolower(Str::random(5)),
                    'category_id'          => $job['category_id'],
                    'location_id'          => $job['location_id'],
                    'created_by'           => $admin->id,
                    'description'          => $job['description'],
                    'requirements'         => $job['requirements'],
                    'benefits'             => $this->benefits(),
                    'responsibilities'     => $job['responsibilities'],
                    'employment_type'      => $job['employment_type'],
                    'experience_level'     => $job['experience_level'],
                    'salary_min'           => $job['salary_min'],
                    'salary_max'           => $job['salary_max'],
                    'salary_currency'      => 'USD',
                    'salary_period'        => 'Annual',
                    'application_deadline' => $deadline->toDateString(),
                    'is_featured'          => $job['is_featured'],
                    'is_published'         => $isPublished,
                    'published_at'         => $isPublished ? $postedAt : null,
                    'views_count'          => $isPublished ? rand(40, 900) : rand(0, 15),
                    'applications_count'   => 0,
                    'created_at'           => $postedAt,
                    'updated_at'           => $now,
                ]);

                if (! $isPublished) {
                    continue; // no applications against unpublished drafts
                }

                $applicants = $job['applicants'];
                foreach ($applicants as $i => $applicant) {
                    // ~1 in 3 applications is linked to a registered user account.
                    $userId = null;
                    $email  = $applicant['email'];
                    if (! empty($registeredApplicants) && $i % 3 === 0) {
                        $userId = array_rand($registeredApplicants);
                        $email  = $registeredApplicants[$userId];
                    }

                    $appliedAt = (clone $postedAt)->addDays(rand(1, max(1, $job['posted_days_ago'] - 1)));
                    $status    = $applicant['status'];
                    $reviewed  = $status !== 'submitted';
                    $reviewedAt = $reviewed
                        ? (clone $appliedAt)->addDays(rand(1, 6))
                        : null;

                    DB::table('job_applications')->insert([
                        'job_posting_id'         => $jobId,
                        'user_id'                => $userId,
                        'first_name'             => $applicant['first_name'],
                        'last_name'              => $applicant['last_name'],
                        'email'                  => $email,
                        'phone'                  => $this->phone(),
                        'cover_letter'           => $this->coverLetter($applicant, $job),
                        'resume_path'            => 'resumes/' . Str::slug($applicant['first_name'] . '-' . $applicant['last_name']) . '-' . strtolower(Str::random(8)) . '.pdf',
                        'portfolio_url'          => $applicant['portfolio_url'] ?? null,
                        'linkedin_url'           => 'https://www.linkedin.com/in/' . Str::slug($applicant['first_name'] . '-' . $applicant['last_name']),
                        'github_url'             => $applicant['github_url'] ?? null,
                        'additional_information' => $applicant['additional_information'] ?? null,
                        'skills'                 => $applicant['skills'],
                        'current_company'        => $applicant['current_company'],
                        'current_position'       => $applicant['current_position'],
                        'education'              => $applicant['education'],
                        'highest_degree'         => $applicant['highest_degree'],
                        'expected_salary'        => $applicant['expected_salary'],
                        'years_of_experience'    => $applicant['years_of_experience'],
                        'referral_source'        => $applicant['referral_source'],
                        'status'                 => $status,
                        'admin_notes'            => $reviewed ? ($applicant['admin_notes'] ?? null) : null,
                        'reviewed_at'            => $reviewedAt,
                        'reviewed_by'            => $reviewed ? $admin->id : null,
                        'created_at'             => $appliedAt,
                        'updated_at'             => $reviewedAt ?? $appliedAt,
                    ]);
                }

                DB::table('job_postings')
                    ->where('id', $jobId)
                    ->update(['applications_count' => count($applicants)]);
            }
        });

        $this->command->info('Seeded ' . DB::table('job_postings')->count() . ' job postings and ' . DB::table('job_applications')->count() . ' applications.');
    }

    /**
     * Shared benefits block — realistic for a mid-size federal contractor.
     */
    private function benefits(): string
    {
        return implode("\n", [
            '• Comprehensive medical, dental, and vision coverage with multiple plan options',
            '• 401(k) with 4% company match, vested immediately',
            '• 15 days PTO in year one (accruing to 25), plus 11 federal holidays',
            '• $5,000 annual budget for training, certifications, and conference attendance',
            '• Security clearance sponsorship and reimbursement for clearance-related costs',
            '• Tuition assistance up to $10,000/year',
            '• Referral bonuses of $3,000–$7,500 for successful cleared hires',
        ]);
    }

    private function phone(): string
    {
        return '+1 (' . rand(202, 703) . ') ' . rand(200, 999) . '-' . str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function coverLetter(array $a, array $job): string
    {
        return "Dear Hiring Team,\n\n"
            . "I'm applying for the {$job['title']} position. I'm currently a {$a['current_position']} at {$a['current_company']}, "
            . "with {$a['years_of_experience']} years of experience working on federal programs. "
            . "My background covers {$a['skills']}, which maps closely to what this role requires.\n\n"
            . "I hold a {$a['highest_degree']} from {$a['education']} and "
            . ($job['category_id'] === 1
                ? "I've spent the last several years supporting cleared environments and delivering to government stakeholders. "
                : "I've delivered results against tight government deadlines and compliance requirements. ")
            . "I'd welcome the chance to discuss how I can contribute to your team.\n\n"
            . "Best regards,\n{$a['first_name']} {$a['last_name']}";
    }

    /**
     * The job postings, each with its own pool of applicants whose profiles
     * are consistent with the role.
     */
    private function jobs(): array
    {
        return [
            // ---------------------------------------------------------------
            [
                'title'            => 'Senior Full Stack Engineer (Laravel/Vue) - Public Sector',
                'category_id'      => 1,
                'location_id'      => 5, // Remote
                'employment_type'  => 'Full-time',
                'experience_level' => 'Senior',
                'salary_min'       => 135000,
                'salary_max'       => 165000,
                'is_featured'      => true,
                'is_published'     => true,
                'posted_days_ago'  => 21,
                'deadline_in_days' => 40,
                'description'      => "GScalable is modernizing case-management systems for a federal civilian agency. You'll own major features end to end across a Laravel API and a Vue 3 front end, working directly with government product owners in an Agile delivery cadence. This is a remote role; U.S. citizenship is required and you must be able to obtain a Public Trust clearance.",
                'requirements'     => implode("\n", [
                    "• 6+ years building production web applications, including 3+ years with PHP/Laravel",
                    "• Strong JavaScript and experience with Vue (or React) in a component-driven codebase",
                    "• Solid relational database skills (MySQL/PostgreSQL), including query optimization",
                    "• Experience with RESTful API design, automated testing (PHPUnit/Pest), and CI/CD",
                    "• U.S. citizenship; ability to pass a Public Trust background investigation",
                    "• Familiarity with Section 508 / WCAG 2.1 AA accessibility requirements is a plus",
                ]),
                'responsibilities' => implode("\n", [
                    "• Design and build features across the Laravel API and Vue SPA",
                    "• Write tests, review pull requests, and keep the CI pipeline green",
                    "• Partner with the government product owner to refine and estimate the backlog",
                    "• Improve performance, observability, and security posture of the platform",
                    "• Mentor two mid-level engineers on the team",
                ]),
                'applicants' => [
                    [
                        'first_name' => 'Marcus', 'last_name' => 'Ellery',
                        'email' => 'marcus.ellery@gmail.com',
                        'skills' => 'PHP, Laravel, Vue.js, MySQL, REST API, CI/CD, PHPUnit, Docker',
                        'current_company' => 'Booz Allen Hamilton', 'current_position' => 'Senior Software Engineer',
                        'education' => 'University of Maryland', 'highest_degree' => "Bachelor's in Computer Science",
                        'expected_salary' => 158000, 'years_of_experience' => 9,
                        'referral_source' => 'LinkedIn', 'status' => 'interview_scheduled',
                        'github_url' => 'https://github.com/mellery-dev',
                        'admin_notes' => 'Strong Laravel depth, active Public Trust. Panel interview set for next week.',
                    ],
                    [
                        'first_name' => 'Priya', 'last_name' => 'Nair',
                        'email' => 'priya.nair.dev@gmail.com',
                        'skills' => 'PHP, Laravel, JavaScript, Vue.js, PostgreSQL, GraphQL, TypeScript',
                        'current_company' => 'ICF', 'current_position' => 'Full Stack Developer',
                        'education' => 'Virginia Tech', 'highest_degree' => "Bachelor's in Computer Engineering",
                        'expected_salary' => 150000, 'years_of_experience' => 7,
                        'referral_source' => 'Employee Referral', 'status' => 'shortlisted',
                        'github_url' => 'https://github.com/priyanair',
                        'admin_notes' => 'Great take-home submission. Shortlisted pending clearance verification.',
                    ],
                    [
                        'first_name' => 'David', 'last_name' => 'Okafor',
                        'email' => 'dokafor.eng@gmail.com',
                        'skills' => 'PHP, Laravel, Vue.js, MySQL, Redis, AWS, Docker, Kubernetes',
                        'current_company' => 'Accenture Federal Services', 'current_position' => 'Software Engineer III',
                        'education' => 'George Mason University', 'highest_degree' => "Master's in Software Engineering",
                        'expected_salary' => 162000, 'years_of_experience' => 8,
                        'referral_source' => 'Company Website', 'status' => 'under_review',
                        'admin_notes' => 'Resume looks strong. Waiting on recruiter screen.',
                    ],
                    [
                        'first_name' => 'Hannah', 'last_name' => 'Weiss',
                        'email' => 'hannah.weiss@gmail.com',
                        'skills' => 'PHP, Laravel, JavaScript, React, MySQL, REST API',
                        'current_company' => 'Leidos', 'current_position' => 'Web Developer',
                        'education' => 'Penn State University', 'highest_degree' => "Bachelor's in Information Sciences",
                        'expected_salary' => 140000, 'years_of_experience' => 5,
                        'referral_source' => 'Indeed', 'status' => 'submitted',
                    ],
                    [
                        'first_name' => 'Tobias', 'last_name' => 'Grant',
                        'email' => 'tobias.grant@gmail.com',
                        'skills' => 'PHP, Symfony, JavaScript, jQuery, MySQL',
                        'current_company' => 'Freelance', 'current_position' => 'Contract Developer',
                        'education' => 'Community College of Denver', 'highest_degree' => "Associate's in Web Development",
                        'expected_salary' => 128000, 'years_of_experience' => 4,
                        'referral_source' => 'Glassdoor', 'status' => 'rejected',
                        'admin_notes' => 'Limited Laravel/Vue exposure and no clearance eligibility path. Passing for now.',
                    ],
                ],
            ],

            // ---------------------------------------------------------------
            [
                'title'            => 'DevSecOps Engineer (AWS GovCloud) - TS/SCI',
                'category_id'      => 1,
                'location_id'      => 4, // London (overseas support site)
                'employment_type'  => 'Full-time',
                'experience_level' => 'Mid-level',
                'salary_min'       => 145000,
                'salary_max'       => 175000,
                'is_featured'      => true,
                'is_published'     => true,
                'posted_days_ago'  => 14,
                'deadline_in_days' => 45,
                'description'      => "Support a DoD customer's continuous-delivery pipeline running in AWS GovCloud. You'll harden infrastructure-as-code, automate STIG compliance, and keep the ATO boundary healthy. An active TS/SCI clearance is required before start.",
                'requirements'     => implode("\n", [
                    "• 4+ years in DevOps/DevSecOps with strong AWS experience (GovCloud a plus)",
                    "• Terraform, Docker, and Kubernetes in production",
                    "• CI/CD tooling (GitLab CI, Jenkins, or GitHub Actions) and pipeline security scanning",
                    "• Working knowledge of NIST 800-53, RMF, and STIG hardening",
                    "• Active TS/SCI clearance; DoD 8570 IAT Level II certification (Security+ or equivalent)",
                ]),
                'responsibilities' => implode("\n", [
                    "• Maintain and extend Terraform modules for the GovCloud environment",
                    "• Automate STIG checks and remediation into the deployment pipeline",
                    "• Manage Kubernetes clusters and container image supply-chain security",
                    "• Support the ISSO with continuous-monitoring evidence and POA&M closure",
                    "• Respond to pipeline incidents during core support hours",
                ]),
                'applicants' => [
                    [
                        'first_name' => 'Rachel', 'last_name' => 'Kim',
                        'email' => 'rachel.kim.ops@gmail.com',
                        'skills' => 'AWS, Terraform, Docker, Kubernetes, GitLab CI, Python, NIST 800-53, STIG',
                        'current_company' => 'CACI International', 'current_position' => 'DevSecOps Engineer',
                        'education' => 'University of Texas at Austin', 'highest_degree' => "Bachelor's in Computer Science",
                        'expected_salary' => 170000, 'years_of_experience' => 6,
                        'referral_source' => 'Employee Referral', 'status' => 'offer_made',
                        'admin_notes' => 'Active TS/SCI with poly. Excellent panel. Offer extended at top of band.',
                    ],
                    [
                        'first_name' => 'Andre', 'last_name' => 'Coleman',
                        'email' => 'andre.coleman@gmail.com',
                        'skills' => 'AWS GovCloud, Terraform, Ansible, Jenkins, Docker, Bash, RMF',
                        'current_company' => 'ManTech', 'current_position' => 'Cloud Engineer',
                        'education' => 'Norfolk State University', 'highest_degree' => "Bachelor's in Information Technology",
                        'expected_salary' => 160000, 'years_of_experience' => 5,
                        'referral_source' => 'ClearanceJobs', 'status' => 'interviewed',
                        'admin_notes' => 'Solid technically. Debrief pending — competing with Kim.',
                    ],
                    [
                        'first_name' => 'Sofia', 'last_name' => 'Marchetti',
                        'email' => 'sofia.marchetti@gmail.com',
                        'skills' => 'AWS, Kubernetes, Helm, GitHub Actions, Go, Prometheus, Grafana',
                        'current_company' => 'Raytheon', 'current_position' => 'Site Reliability Engineer',
                        'education' => 'Carnegie Mellon University', 'highest_degree' => "Master's in Information Security",
                        'expected_salary' => 175000, 'years_of_experience' => 7,
                        'referral_source' => 'LinkedIn', 'status' => 'under_review',
                        'admin_notes' => 'Strong SRE background; confirming SCI is current (was in access 8 months ago).',
                    ],
                    [
                        'first_name' => 'Nathan', 'last_name' => 'Brooks',
                        'email' => 'nathan.brooks@gmail.com',
                        'skills' => 'AWS, Terraform, Docker, Jenkins, Python',
                        'current_company' => 'General Dynamics IT', 'current_position' => 'Systems Administrator',
                        'education' => 'University of Central Florida', 'highest_degree' => "Bachelor's in Computer Engineering",
                        'expected_salary' => 148000, 'years_of_experience' => 4,
                        'referral_source' => 'Job Fair', 'status' => 'submitted',
                    ],
                ],
            ],

            // ---------------------------------------------------------------
            [
                'title'            => 'Data Scientist - Federal Analytics Program',
                'category_id'      => 1,
                'location_id'      => 1, // New York
                'employment_type'  => 'Full-time',
                'experience_level' => 'Mid-level',
                'salary_min'       => 120000,
                'salary_max'       => 150000,
                'is_featured'      => false,
                'is_published'     => true,
                'posted_days_ago'  => 30,
                'deadline_in_days' => 25,
                'description'      => "Join a small analytics team delivering forecasting and anomaly-detection models for a federal financial-oversight client. You'll work in Python across the full modeling lifecycle and present findings to non-technical government stakeholders. Public Trust required; sponsorship available.",
                'requirements'     => implode("\n", [
                    "• 3+ years applying machine learning to real business problems",
                    "• Strong Python (pandas, scikit-learn) and SQL",
                    "• Experience communicating model results to non-technical audiences",
                    "• Comfortable with reproducible workflows (Git, notebooks, model versioning)",
                    "• Ability to obtain a Public Trust clearance (U.S. person)",
                ]),
                'responsibilities' => implode("\n", [
                    "• Build, validate, and monitor predictive models against agency data",
                    "• Translate ambiguous mission questions into analytical approaches",
                    "• Produce clear written analyses and briefing materials",
                    "• Collaborate with data engineers on feature pipelines",
                ]),
                'applicants' => [
                    [
                        'first_name' => 'Elena', 'last_name' => 'Vasquez',
                        'email' => 'elena.vasquez.ds@gmail.com',
                        'skills' => 'Python, pandas, scikit-learn, SQL, PostgreSQL, statistical modeling, data visualization',
                        'current_company' => 'MITRE', 'current_position' => 'Data Scientist',
                        'education' => 'Columbia University', 'highest_degree' => "Master's in Statistics",
                        'expected_salary' => 145000, 'years_of_experience' => 5,
                        'referral_source' => 'LinkedIn', 'status' => 'hired',
                        'admin_notes' => 'Accepted offer. Start date confirmed, Public Trust already in place.',
                        'additional_information' => 'Available to start with two weeks notice.',
                    ],
                    [
                        'first_name' => 'James', 'last_name' => 'Whitfield',
                        'email' => 'james.whitfield@gmail.com',
                        'skills' => 'Python, scikit-learn, TensorFlow, SQL, MongoDB, A/B testing',
                        'current_company' => 'Deloitte', 'current_position' => 'Analytics Consultant',
                        'education' => 'New York University', 'highest_degree' => "Bachelor's in Applied Mathematics",
                        'expected_salary' => 138000, 'years_of_experience' => 4,
                        'referral_source' => 'Glassdoor', 'status' => 'rejected',
                        'admin_notes' => 'Good candidate but role filled by Vasquez. Keep warm for next req.',
                    ],
                    [
                        'first_name' => 'Mei', 'last_name' => 'Chen',
                        'email' => 'mei.chen.analytics@gmail.com',
                        'skills' => 'Python, pandas, SQL, R, forecasting, time series',
                        'current_company' => 'Federal Reserve Bank of New York', 'current_position' => 'Research Analyst',
                        'education' => 'Cornell University', 'highest_degree' => "Master's in Economics",
                        'expected_salary' => 132000, 'years_of_experience' => 3,
                        'referral_source' => 'University Career Center', 'status' => 'rejected',
                        'admin_notes' => 'Strong economics/forecasting but light on production ML. Position closed.',
                    ],
                ],
            ],

            // ---------------------------------------------------------------
            [
                'title'            => 'Help Desk Analyst (Tier 2) - On-Site Federal Support',
                'category_id'      => 5, // Customer Support
                'location_id'      => 3, // Austin
                'employment_type'  => 'Full-time',
                'experience_level' => 'Junior',
                'salary_min'       => 52000,
                'salary_max'       => 64000,
                'is_featured'      => false,
                'is_published'     => true,
                'posted_days_ago'  => 10,
                'deadline_in_days' => 30,
                'description'      => "Provide Tier 2 desk-side and remote support for roughly 400 federal employees at a facility in Austin. You'll resolve escalations from Tier 1, image and deploy workstations, and manage tickets to SLA in ServiceNow. This is a fully on-site role.",
                'requirements'     => implode("\n", [
                    "• 2+ years in a help desk or desktop support role",
                    "• Windows 10/11 troubleshooting, Active Directory account administration, and O365",
                    "• Experience working tickets to SLA in ServiceNow or a similar ITSM tool",
                    "• CompTIA A+ (or ability to obtain within 90 days)",
                    "• Ability to pass a federal background investigation (Public Trust)",
                ]),
                'responsibilities' => implode("\n", [
                    "• Own escalated incidents and service requests from Tier 1",
                    "• Image, configure, and deploy laptops and desktops",
                    "• Perform account and group management in Active Directory",
                    "• Document resolutions and contribute to the knowledge base",
                    "• Meet or exceed SLA targets for response and resolution",
                ]),
                'applicants' => [
                    [
                        'first_name' => 'Carlos', 'last_name' => 'Mendez',
                        'email' => 'carlos.mendez.it@gmail.com',
                        'skills' => 'Windows 10, Active Directory, Office 365, ServiceNow, hardware troubleshooting, CompTIA A+',
                        'current_company' => 'Unisys', 'current_position' => 'Help Desk Technician',
                        'education' => 'Austin Community College', 'highest_degree' => "Associate's in Network Administration",
                        'expected_salary' => 60000, 'years_of_experience' => 3,
                        'referral_source' => 'Indeed', 'status' => 'offer_accepted',
                        'admin_notes' => 'A+ certified, local to Austin, interviewed very well. Offer accepted; onboarding scheduled.',
                    ],
                    [
                        'first_name' => 'Ashley', 'last_name' => 'Trent',
                        'email' => 'ashley.trent@gmail.com',
                        'skills' => 'Windows 11, Active Directory, Intune, ticketing systems, customer service',
                        'current_company' => 'Dell Technologies', 'current_position' => 'Technical Support Representative',
                        'education' => 'Texas State University', 'highest_degree' => "Bachelor's in Communication",
                        'expected_salary' => 58000, 'years_of_experience' => 2,
                        'referral_source' => 'Company Website', 'status' => 'interview_scheduled',
                        'admin_notes' => 'Backup candidate — phone screen strong. Holding interview slot in case primary declines.',
                    ],
                    [
                        'first_name' => 'Devin', 'last_name' => 'Parker',
                        'email' => 'devin.parker@gmail.com',
                        'skills' => 'Windows, macOS, Google Workspace, Jira Service Management',
                        'current_company' => 'Indeed', 'current_position' => 'IT Support Specialist',
                        'education' => 'University of Houston', 'highest_degree' => "Bachelor's in Information Systems",
                        'expected_salary' => 62000, 'years_of_experience' => 3,
                        'referral_source' => 'LinkedIn', 'status' => 'under_review',
                    ],
                    [
                        'first_name' => 'Brianna', 'last_name' => 'Holt',
                        'email' => 'brianna.holt@gmail.com',
                        'skills' => 'Windows 10, basic networking, customer service, remote support',
                        'current_company' => 'Best Buy (Geek Squad)', 'current_position' => 'Consultation Agent',
                        'education' => 'Austin Community College', 'highest_degree' => 'High School Diploma',
                        'expected_salary' => 50000, 'years_of_experience' => 1,
                        'referral_source' => 'Job Fair', 'status' => 'submitted',
                    ],
                    [
                        'first_name' => 'Omar', 'last_name' => 'Haddad',
                        'email' => 'omar.haddad@gmail.com',
                        'skills' => 'Windows, Active Directory, SCCM, ServiceNow, PowerShell scripting',
                        'current_company' => 'Peraton', 'current_position' => 'Desktop Support Analyst',
                        'education' => 'DeVry University', 'highest_degree' => "Bachelor's in Network and Communications Management",
                        'expected_salary' => 64000, 'years_of_experience' => 4,
                        'referral_source' => 'Employee Referral', 'status' => 'submitted',
                    ],
                ],
            ],

            // ---------------------------------------------------------------
            [
                'title'            => 'Proposal Manager - Federal Capture & BD',
                'category_id'      => 4, // Sales
                'location_id'      => 4, // London -> treat as HQ; actually use New York
                'employment_type'  => 'Full-time',
                'experience_level' => 'Senior',
                'salary_min'       => 115000,
                'salary_max'       => 140000,
                'is_featured'      => true,
                'is_published'     => true,
                'posted_days_ago'  => 18,
                'deadline_in_days' => 35,
                'description'      => "Own the proposal lifecycle for federal opportunities from RFP release through submission. You'll run color-team reviews, manage writers and SMEs against a compliance matrix, and produce compliant, compelling volumes under deadline pressure. Shipley process experience strongly preferred.",
                'requirements'     => implode("\n", [
                    "• 6+ years managing federal proposals end to end",
                    "• Deep command of the Shipley process and FAR-driven compliance",
                    "• Track record leading Pink/Red/Gold team reviews",
                    "• Excellent writing and editing; able to turn SME input into clear narrative",
                    "• APMP certification a plus",
                ]),
                'responsibilities' => implode("\n", [
                    "• Build and maintain the compliance matrix and proposal schedule",
                    "• Facilitate color-team reviews and adjudicate comments",
                    "• Manage volume leads, writers, and graphics to milestones",
                    "• Ensure on-time, fully compliant submission through SAM.gov / agency portals",
                    "• Contribute to win-theme and past-performance development during capture",
                ]),
                'applicants' => [
                    [
                        'first_name' => 'Katherine', 'last_name' => 'Boyd',
                        'email' => 'katherine.boyd@gmail.com',
                        'skills' => 'Shipley process, proposal management, compliance matrix, color team reviews, APMP, technical writing',
                        'current_company' => 'SAIC', 'current_position' => 'Senior Proposal Manager',
                        'education' => 'American University', 'highest_degree' => "Bachelor's in English",
                        'expected_salary' => 138000, 'years_of_experience' => 11,
                        'referral_source' => 'Professional Network', 'status' => 'interview_scheduled',
                        'admin_notes' => 'APMP Practitioner, ran $500M+ recompetes. Strong fit. Final interview with BD VP scheduled.',
                    ],
                    [
                        'first_name' => 'Gregory', 'last_name' => 'Sana',
                        'email' => 'gregory.sana@gmail.com',
                        'skills' => 'Proposal writing, Shipley, past performance, orals coaching, editing',
                        'current_company' => 'Guidehouse', 'current_position' => 'Proposal Manager',
                        'education' => 'University of Virginia', 'highest_degree' => "Master's in Public Administration",
                        'expected_salary' => 130000, 'years_of_experience' => 8,
                        'referral_source' => 'LinkedIn', 'status' => 'shortlisted',
                        'admin_notes' => 'Solid writer, good references. Shortlisted behind Boyd.',
                    ],
                    [
                        'first_name' => 'Denise', 'last_name' => 'Fowler',
                        'email' => 'denise.fowler@gmail.com',
                        'skills' => 'Proposal coordination, desktop publishing, InDesign, compliance review',
                        'current_company' => 'Amentum', 'current_position' => 'Proposal Coordinator',
                        'education' => 'George Washington University', 'highest_degree' => "Bachelor's in Journalism",
                        'expected_salary' => 108000, 'years_of_experience' => 5,
                        'referral_source' => 'Indeed', 'status' => 'rejected',
                        'admin_notes' => 'More coordinator than manager at this point. Encouraged to reapply in 12–18 months.',
                    ],
                ],
            ],

            // ---------------------------------------------------------------
            [
                'title'            => 'Senior DCAA Compliance Accountant',
                'category_id'      => 7, // Finance
                'location_id'      => 1, // New York
                'employment_type'  => 'Full-time',
                'experience_level' => 'Senior',
                'salary_min'       => 98000,
                'salary_max'       => 122000,
                'is_featured'      => false,
                'is_published'     => true,
                'posted_days_ago'  => 25,
                'deadline_in_days' => 20,
                'description'      => "Manage government contract accounting in a DCAA-compliant environment. You'll oversee incurred-cost submissions, indirect rate calculations, and project setup in Unanet, and serve as a point of contact during DCAA audits. CPA preferred.",
                'requirements'     => implode("\n", [
                    "• 6+ years of government contract accounting experience",
                    "• Hands-on with incurred cost submissions (ICS/ICE) and provisional billing rates",
                    "• Working knowledge of FAR Part 31, CAS, and DCAA audit expectations",
                    "• Experience with Unanet, Deltek Costpoint, or a comparable GovCon ERP",
                    "• CPA or CPA candidate preferred",
                ]),
                'responsibilities' => implode("\n", [
                    "• Prepare and file the annual incurred cost submission",
                    "• Calculate and monitor indirect rates; recommend adjustments",
                    "• Set up and maintain projects, funding, and billing in Unanet",
                    "• Support monthly close, contract invoicing, and revenue recognition",
                    "• Act as liaison for DCAA and DCMA audit requests",
                ]),
                'applicants' => [
                    [
                        'first_name' => 'Robert', 'last_name' => 'Nguyen',
                        'email' => 'robert.nguyen.cpa@gmail.com',
                        'skills' => 'DCAA compliance, incurred cost submission, indirect rates, FAR Part 31, Unanet, Excel, CPA',
                        'current_company' => 'BDO USA', 'current_position' => 'Government Contracts Manager',
                        'education' => 'Baruch College, CUNY', 'highest_degree' => "Master's in Accounting",
                        'expected_salary' => 120000, 'years_of_experience' => 10,
                        'referral_source' => 'Professional Network', 'status' => 'offer_made',
                        'admin_notes' => 'Licensed CPA, led three clean DCAA audits. Offer out at $118K + bonus.',
                    ],
                    [
                        'first_name' => 'Yolanda', 'last_name' => 'Bruce',
                        'email' => 'yolanda.bruce@gmail.com',
                        'skills' => 'Deltek Costpoint, contract billing, revenue recognition, FAR, CAS, month-end close',
                        'current_company' => 'Parsons Corporation', 'current_position' => 'Senior Cost Accountant',
                        'education' => 'Rutgers University', 'highest_degree' => "Bachelor's in Accounting",
                        'expected_salary' => 112000, 'years_of_experience' => 8,
                        'referral_source' => 'LinkedIn', 'status' => 'interviewed',
                        'admin_notes' => 'Strong Costpoint background, needs Unanet ramp-up. Debrief in progress.',
                    ],
                    [
                        'first_name' => 'Patrick', 'last_name' => 'Doyle',
                        'email' => 'patrick.doyle@gmail.com',
                        'skills' => 'General ledger, AP/AR, QuickBooks, financial reporting',
                        'current_company' => 'Regional CPA Firm', 'current_position' => 'Staff Accountant',
                        'education' => 'Fordham University', 'highest_degree' => "Bachelor's in Finance",
                        'expected_salary' => 95000, 'years_of_experience' => 4,
                        'referral_source' => 'Indeed', 'status' => 'rejected',
                        'admin_notes' => 'No GovCon/DCAA exposure. Not a fit for a senior compliance seat.',
                    ],
                ],
            ],

            // ---------------------------------------------------------------
            [
                'title'            => 'Technical Recruiter - Cleared IT Talent',
                'category_id'      => 6, // Human Resources
                'location_id'      => 5, // Remote
                'employment_type'  => 'Full-time',
                'experience_level' => 'Mid-level',
                'salary_min'       => 70000,
                'salary_max'       => 90000,
                'is_featured'      => false,
                'is_published'     => true,
                'posted_days_ago'  => 8,
                'deadline_in_days' => 42,
                'description'      => "Full-desk recruiting for cleared software, cloud, and cyber roles across our federal contracts. You'll source on ClearanceJobs and LinkedIn Recruiter, screen for clearance level and technical fit, and shepherd candidates through offer and onboarding. Remote, with occasional travel to job fairs.",
                'requirements'     => implode("\n", [
                    "• 3+ years of full-cycle technical recruiting, ideally in GovCon",
                    "• Familiarity with clearance levels (Public Trust through TS/SCI) and adjudication timelines",
                    "• Experience with an ATS (Greenhouse, iCIMS, or similar) and Boolean sourcing",
                    "• Strong candidate-experience instincts and closing skills",
                ]),
                'responsibilities' => implode("\n", [
                    "• Own requisitions from intake through offer acceptance",
                    "• Source and pipeline cleared candidates for hard-to-fill roles",
                    "• Run phone screens assessing technical and clearance fit",
                    "• Partner with hiring managers on calibration and feedback loops",
                    "• Keep the ATS clean and report on funnel metrics weekly",
                ]),
                'applicants' => [
                    [
                        'first_name' => 'Monica', 'last_name' => 'Reyes',
                        'email' => 'monica.reyes.ta@gmail.com',
                        'skills' => 'Full-cycle recruiting, ClearanceJobs, LinkedIn Recruiter, Greenhouse, Boolean sourcing, offer negotiation',
                        'current_company' => 'GDIT', 'current_position' => 'Technical Recruiter',
                        'education' => 'University of Maryland', 'highest_degree' => "Bachelor's in Human Resource Management",
                        'expected_salary' => 88000, 'years_of_experience' => 5,
                        'referral_source' => 'Employee Referral', 'status' => 'shortlisted',
                        'admin_notes' => 'Recruits the exact profiles we need. Reference checks underway.',
                    ],
                    [
                        'first_name' => 'Tyler', 'last_name' => 'Rhodes',
                        'email' => 'tyler.rhodes@gmail.com',
                        'skills' => 'Agency recruiting, iCIMS, cold outreach, pipeline management, IT staffing',
                        'current_company' => 'TEKsystems', 'current_position' => 'Recruiter',
                        'education' => 'Towson University', 'highest_degree' => "Bachelor's in Business Administration",
                        'expected_salary' => 80000, 'years_of_experience' => 4,
                        'referral_source' => 'LinkedIn', 'status' => 'under_review',
                    ],
                    [
                        'first_name' => 'Grace', 'last_name' => 'Abbott',
                        'email' => 'grace.abbott@gmail.com',
                        'skills' => 'Recruiting coordination, scheduling, candidate experience, Workday',
                        'current_company' => 'Northrop Grumman', 'current_position' => 'Recruiting Coordinator',
                        'education' => 'James Madison University', 'highest_degree' => "Bachelor's in Psychology",
                        'expected_salary' => 68000, 'years_of_experience' => 2,
                        'referral_source' => 'Company Website', 'status' => 'submitted',
                    ],
                ],
            ],

            // ---------------------------------------------------------------
            [
                'title'            => 'UX/UI Designer - Government Digital Services',
                'category_id'      => 2, // Design
                'location_id'      => 2, // San Francisco
                'employment_type'  => 'Full-time',
                'experience_level' => 'Mid-level',
                'salary_min'       => 105000,
                'salary_max'       => 130000,
                'is_featured'      => false,
                'is_published'     => true,
                'posted_days_ago'  => 12,
                'deadline_in_days' => 33,
                'description'      => "Design accessible, human-centered interfaces for public-facing federal applications. You'll run research sessions with real users, build prototypes in Figma, and work within the U.S. Web Design System. Section 508 conformance is non-negotiable on this program.",
                'requirements'     => implode("\n", [
                    "• 3+ years of product design experience with a portfolio of shipped work",
                    "• Strong Figma skills and a solid grasp of interaction and visual design",
                    "• Hands-on experience meeting WCAG 2.1 AA / Section 508 requirements",
                    "• Comfortable planning and moderating usability research",
                    "• Familiarity with the U.S. Web Design System (USWDS) a plus",
                ]),
                'responsibilities' => implode("\n", [
                    "• Turn requirements and research into wireframes, prototypes, and specs",
                    "• Plan and run moderated usability sessions; synthesize findings",
                    "• Maintain and extend the program's USWDS-based component library",
                    "• Partner with engineers to ensure the build matches the design and passes 508 review",
                ]),
                'applicants' => [
                    [
                        'first_name' => 'Nina', 'last_name' => 'Petrova',
                        'email' => 'nina.petrova.ux@gmail.com',
                        'skills' => 'Figma, UX Design, UI Design, usability testing, accessibility, WCAG 2.1, USWDS, prototyping',
                        'current_company' => '18F / GSA (contractor)', 'current_position' => 'Product Designer',
                        'education' => 'California College of the Arts', 'highest_degree' => "Bachelor's in Interaction Design",
                        'expected_salary' => 128000, 'years_of_experience' => 6,
                        'referral_source' => 'Professional Network', 'status' => 'interview_scheduled',
                        'portfolio_url' => 'https://ninapetrova.design',
                        'admin_notes' => 'Directly relevant civic-tech portfolio and 508 depth. Portfolio review + panel scheduled.',
                    ],
                    [
                        'first_name' => 'Liam', 'last_name' => 'Foster',
                        'email' => 'liam.foster.design@gmail.com',
                        'skills' => 'Figma, Adobe XD, design systems, prototyping, user research',
                        'current_company' => 'Pivotal Labs', 'current_position' => 'UX Designer',
                        'education' => 'San Jose State University', 'highest_degree' => "Bachelor's in Graphic Design",
                        'expected_salary' => 118000, 'years_of_experience' => 4,
                        'referral_source' => 'LinkedIn', 'status' => 'under_review',
                        'portfolio_url' => 'https://liamfoster.work',
                    ],
                    [
                        'first_name' => 'Aisha', 'last_name' => 'Rahman',
                        'email' => 'aisha.rahman.ux@gmail.com',
                        'skills' => 'Figma, Sketch, visual design, branding, marketing sites',
                        'current_company' => 'Freelance', 'current_position' => 'Freelance Designer',
                        'education' => 'Academy of Art University', 'highest_degree' => "Bachelor's in Web Design",
                        'expected_salary' => 110000, 'years_of_experience' => 5,
                        'referral_source' => 'Indeed', 'status' => 'submitted',
                        'portfolio_url' => 'https://aisharahman.myportfolio.com',
                    ],
                    [
                        'first_name' => 'Kevin', 'last_name' => 'Ash',
                        'email' => 'kevin.ash@gmail.com',
                        'skills' => 'Figma, HTML, CSS, front-end handoff, accessibility basics',
                        'current_company' => 'Salesforce', 'current_position' => 'Junior Product Designer',
                        'education' => 'University of California, Davis', 'highest_degree' => "Bachelor's in Design",
                        'expected_salary' => 102000, 'years_of_experience' => 2,
                        'referral_source' => 'Glassdoor', 'status' => 'rejected',
                        'admin_notes' => 'Promising but junior for this seat; no government or 508 experience yet.',
                    ],
                ],
            ],

            // ---------------------------------------------------------------
            [
                'title'            => 'Digital Content Strategist - Public Sector Marketing',
                'category_id'      => 3, // Marketing
                'location_id'      => 5, // Remote
                'employment_type'  => 'Contract',
                'experience_level' => 'Mid-level',
                'salary_min'       => 80000,
                'salary_max'       => 100000,
                'is_featured'      => false,
                'is_published'     => true,
                'posted_days_ago'  => 6,
                'deadline_in_days' => 28,
                'description'      => "12-month contract supporting a federal agency's public-communications team. You'll plan and produce plain-language web content, run the editorial calendar, and report on engagement. Must write to a 6th–8th grade reading level and follow federal plain-language guidelines.",
                'requirements'     => implode("\n", [
                    "• 4+ years in content strategy or digital communications",
                    "• Portfolio of published web content, ideally for government or a regulated industry",
                    "• Working knowledge of plainlanguage.gov guidelines and basic SEO",
                    "• Comfortable in a CMS (Drupal or WordPress) and with analytics (GA4)",
                ]),
                'responsibilities' => implode("\n", [
                    "• Own the editorial calendar and content governance process",
                    "• Draft, edit, and publish plain-language web pages and articles",
                    "• Coordinate reviews with subject-matter experts and public affairs",
                    "• Report monthly on traffic, engagement, and content performance",
                ]),
                'applicants' => [
                    [
                        'first_name' => 'Danielle', 'last_name' => 'Cross',
                        'email' => 'danielle.cross@gmail.com',
                        'skills' => 'Content Strategy, Content Marketing, SEO, plain language, editorial calendar, Drupal, GA4',
                        'current_company' => 'CDC (contractor via Deloitte)', 'current_position' => 'Content Strategist',
                        'education' => 'University of North Carolina', 'highest_degree' => "Bachelor's in Journalism",
                        'expected_salary' => 98000, 'years_of_experience' => 7,
                        'referral_source' => 'LinkedIn', 'status' => 'shortlisted',
                        'portfolio_url' => 'https://daniellecross.contently.com',
                        'admin_notes' => 'Federal health-comms background is a direct match. Shortlisted for final round.',
                    ],
                    [
                        'first_name' => 'Marcus', 'last_name' => 'Bell',
                        'email' => 'marcus.bell.content@gmail.com',
                        'skills' => 'Copywriting, SEO, WordPress, social media, email marketing',
                        'current_company' => 'HubSpot', 'current_position' => 'Content Marketing Manager',
                        'education' => 'Boston University', 'highest_degree' => "Bachelor's in English",
                        'expected_salary' => 92000, 'years_of_experience' => 5,
                        'referral_source' => 'Indeed', 'status' => 'under_review',
                    ],
                    [
                        'first_name' => 'Sara', 'last_name' => 'Lindqvist',
                        'email' => 'sara.lindqvist@gmail.com',
                        'skills' => 'Technical writing, documentation, style guides, Markdown, plain language',
                        'current_company' => 'MongoDB', 'current_position' => 'Technical Writer',
                        'education' => 'University of Washington', 'highest_degree' => "Bachelor's in Technical Communication",
                        'expected_salary' => 88000, 'years_of_experience' => 6,
                        'referral_source' => 'Professional Network', 'status' => 'submitted',
                    ],
                ],
            ],

            // ---------------------------------------------------------------
            [
                'title'            => 'Program Manager - Federal IT Services (PMP)',
                'category_id'      => 4, // Sales / delivery leadership
                'location_id'      => 3, // Austin
                'employment_type'  => 'Full-time',
                'experience_level' => 'Lead',
                'salary_min'       => 150000,
                'salary_max'       => 185000,
                'is_featured'      => true,
                'is_published'     => true,
                'posted_days_ago'  => 27,
                'deadline_in_days' => 50,
                'description'      => "Own delivery and P&L for a $12M/year federal IT services contract. You'll manage a 20-person cross-functional team, be the primary interface to the government COR, and keep CPARS ratings strong. PMP required; active or reinstatable Secret clearance preferred.",
                'requirements'     => implode("\n", [
                    "• 8+ years managing federal IT programs, including full P&L ownership",
                    "• Active PMP certification",
                    "• Experience managing to a PWS/SOW, CDRLs, and CPARS",
                    "• Strong client-facing communication with government stakeholders",
                    "• Secret clearance (active or eligible for reinstatement) preferred",
                ]),
                'responsibilities' => implode("\n", [
                    "• Deliver contract scope on schedule and within budget; own the program P&L",
                    "• Serve as primary point of contact to the COR and government leadership",
                    "• Manage staffing, subcontractors, and CDRL submissions",
                    "• Run program reviews, risk management, and monthly status reporting",
                    "• Support award-fee and option-year positioning",
                ]),
                'applicants' => [
                    [
                        'first_name' => 'Gerald', 'last_name' => 'Winters',
                        'email' => 'gerald.winters.pmp@gmail.com',
                        'skills' => 'Program management, P&L ownership, PMP, CPARS, PWS/SOW, EVM, subcontractor management, risk management',
                        'current_company' => 'Leidos', 'current_position' => 'Program Manager',
                        'education' => 'University of Texas at Austin', 'highest_degree' => "Master's in Business Administration",
                        'expected_salary' => 182000, 'years_of_experience' => 14,
                        'referral_source' => 'Professional Network', 'status' => 'interviewed',
                        'admin_notes' => 'Active Secret, ran a comparable-size program with Exceptional CPARS. Strong debrief; checking references.',
                    ],
                    [
                        'first_name' => 'Angela', 'last_name' => 'Dorsey',
                        'email' => 'angela.dorsey@gmail.com',
                        'skills' => 'Project management, PMP, Agile delivery, stakeholder management, budgeting, MS Project',
                        'current_company' => 'Accenture Federal Services', 'current_position' => 'Senior Project Manager',
                        'education' => 'Howard University', 'highest_degree' => "Bachelor's in Systems Engineering",
                        'expected_salary' => 168000, 'years_of_experience' => 10,
                        'referral_source' => 'LinkedIn', 'status' => 'shortlisted',
                        'admin_notes' => 'PMP, strong delivery record, less P&L exposure than Winters. Solid #2.',
                    ],
                    [
                        'first_name' => 'Paul', 'last_name' => 'Iverson',
                        'email' => 'paul.iverson@gmail.com',
                        'skills' => 'Scrum, Jira, team leadership, roadmap planning, vendor management',
                        'current_company' => 'Oracle', 'current_position' => 'Delivery Manager',
                        'education' => 'Arizona State University', 'highest_degree' => "Bachelor's in Management Information Systems",
                        'expected_salary' => 160000, 'years_of_experience' => 9,
                        'referral_source' => 'Indeed', 'status' => 'rejected',
                        'admin_notes' => 'Commercial delivery background only; no federal contract or clearance experience.',
                    ],
                    [
                        'first_name' => 'Teresa', 'last_name' => 'Salazar',
                        'email' => 'teresa.salazar@gmail.com',
                        'skills' => 'Program management, PMP, ITIL, service delivery, CPARS, CDRL management',
                        'current_company' => 'V2X', 'current_position' => 'Deputy Program Manager',
                        'education' => 'Texas A&M University', 'highest_degree' => "Master's in Project Management",
                        'expected_salary' => 172000, 'years_of_experience' => 11,
                        'referral_source' => 'Employee Referral', 'status' => 'submitted',
                    ],
                ],
            ],

            // ---------------------------------------------------------------
            [
                'title'            => 'Junior Java Developer - Modernization Program',
                'category_id'      => 1, // Software Development
                'location_id'      => 1, // New York
                'employment_type'  => 'Full-time',
                'experience_level' => 'Entry',
                'salary_min'       => 78000,
                'salary_max'       => 95000,
                'is_featured'      => false,
                'is_published'     => true,
                'posted_days_ago'  => 5,
                'deadline_in_days' => 38,
                'description'      => "Entry-level role on a team re-platforming a legacy federal system onto Spring Boot microservices. You'll pair with senior engineers, pick up tickets from the backlog, and grow into a full contributor. We sponsor Public Trust clearances and pay for the first AWS certification.",
                'requirements'     => implode("\n", [
                    "• 0–2 years of professional software experience, or a strong CS degree with project work",
                    "• Working knowledge of Java and object-oriented design",
                    "• Exposure to Spring or Spring Boot, SQL, and Git",
                    "• Eagerness to learn and take feedback in code review",
                    "• U.S. person, able to obtain a Public Trust clearance",
                ]),
                'responsibilities' => implode("\n", [
                    "• Implement well-scoped features and bug fixes with senior guidance",
                    "• Write unit tests and participate in code review",
                    "• Learn the domain and contribute to team ceremonies",
                    "• Help document the new services as they're built",
                ]),
                'applicants' => [
                    [
                        'first_name' => 'Isaiah', 'last_name' => 'Bennett',
                        'email' => 'isaiah.bennett.dev@gmail.com',
                        'skills' => 'Java, Spring Boot, SQL, Git, REST API, JUnit',
                        'current_company' => 'Revature', 'current_position' => 'Associate Software Engineer',
                        'education' => 'Rutgers University', 'highest_degree' => "Bachelor's in Computer Science",
                        'expected_salary' => 90000, 'years_of_experience' => 1,
                        'referral_source' => 'University Career Center', 'status' => 'interview_scheduled',
                        'github_url' => 'https://github.com/isaiahbennett',
                        'admin_notes' => 'CS grad with a real Spring Boot bootcamp project. Coding interview scheduled.',
                    ],
                    [
                        'first_name' => 'Chloe', 'last_name' => 'Adkins',
                        'email' => 'chloe.adkins@gmail.com',
                        'skills' => 'Java, Python, data structures, SQL, Git',
                        'current_company' => 'New Grad', 'current_position' => 'Recent Graduate',
                        'education' => 'Stony Brook University', 'highest_degree' => "Bachelor's in Computer Science",
                        'expected_salary' => 82000, 'years_of_experience' => 0,
                        'referral_source' => 'Job Fair', 'status' => 'under_review',
                        'github_url' => 'https://github.com/chloeadkins',
                    ],
                    [
                        'first_name' => 'Marcus', 'last_name' => 'Idris',
                        'email' => 'marcus.idris@gmail.com',
                        'skills' => 'Java, JavaScript, HTML, CSS, Node.js',
                        'current_company' => 'Freelance', 'current_position' => 'Junior Web Developer',
                        'education' => 'CUNY City College', 'highest_degree' => "Bachelor's in Information Systems",
                        'expected_salary' => 85000, 'years_of_experience' => 2,
                        'referral_source' => 'Indeed', 'status' => 'submitted',
                    ],
                    [
                        'first_name' => 'Grace', 'last_name' => 'Yoon',
                        'email' => 'grace.yoon.swe@gmail.com',
                        'skills' => 'Java, Spring, PostgreSQL, Docker, Git, Agile',
                        'current_company' => 'JPMorgan Chase (internship)', 'current_position' => 'Software Engineering Intern',
                        'education' => 'New York University', 'highest_degree' => "Bachelor's in Computer Science",
                        'expected_salary' => 94000, 'years_of_experience' => 1,
                        'referral_source' => 'LinkedIn', 'status' => 'submitted',
                        'github_url' => 'https://github.com/graceyoon',
                    ],
                ],
            ],

            // ---------------------------------------------------------------
            // One unpublished draft, so the admin listing has a "Draft" row.
            [
                'title'            => 'Cybersecurity Analyst (SOC) - Tier 3',
                'category_id'      => 1,
                'location_id'      => 2,
                'employment_type'  => 'Full-time',
                'experience_level' => 'Senior',
                'salary_min'       => 125000,
                'salary_max'       => 155000,
                'is_featured'      => false,
                'is_published'     => false,
                'posted_days_ago'  => 3,
                'deadline_in_days' => 60,
                'description'      => "DRAFT — pending contract award. Tier 3 SOC analyst performing incident response, threat hunting, and forensic analysis for a federal customer. Requires an active TS/SCI and CISSP or GCIH.",
                'requirements'     => implode("\n", [
                    "• 5+ years in a SOC or incident-response role",
                    "• Deep experience with SIEM (Splunk/Elastic), EDR, and packet analysis",
                    "• CISSP, GCIH, or GCFA",
                    "• Active TS/SCI clearance",
                ]),
                'responsibilities' => implode("\n", [
                    "• Lead response to escalated security incidents",
                    "• Develop and run threat-hunt hypotheses",
                    "• Perform host and network forensic analysis",
                    "• Tune detections and mentor Tier 1/2 analysts",
                ]),
                'applicants' => [],
            ],
        ];
    }
}
