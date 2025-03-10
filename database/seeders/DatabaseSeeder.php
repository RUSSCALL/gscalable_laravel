<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\JobSkill;
use App\Models\JobPosting;
use App\Models\JobCategory;
use App\Models\JobLocation;
use Illuminate\Support\Str;
use App\Models\JobApplication;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            JobCategorySeeder::class,
            JobLocationSeeder::class,
            JobSkillSeeder::class,
            JobPostingSeeder::class,
            JobApplicationSeeder::class,
        ]);
    }
}

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create([
            'role_name' => 'SuperAdmin',
            'updated_at' => now(),
            'created_at' => now()
        ]);
        Role::create([
            'role_name' => 'Admin',
            'updated_at' => now(),
            'created_at' => now()
        ]);
        Role::create([
            'role_name' => 'job_applicant',
            'updated_at' => now(),
            'created_at' => now()
        ]);
    }
}
class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
        
        // Create more users if needed
        User::factory(5)->create();
    }
}

class JobCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Software Development',
                'description' => 'Roles focused on coding and software engineering',
                'is_active' => true,
            ],
            [
                'name' => 'Design',
                'description' => 'Graphic design, UX/UI, and visual design roles',
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'description' => 'Digital marketing, content creation, and SEO roles',
                'is_active' => true,
            ],
            [
                'name' => 'Sales',
                'description' => 'Business development, account management, and sales roles',
                'is_active' => true,
            ],
            [
                'name' => 'Customer Support',
                'description' => 'Customer service and technical support roles',
                'is_active' => true,
            ],
            [
                'name' => 'Human Resources',
                'description' => 'Recruitment, HR management, and people operations',
                'is_active' => true,
            ],
            [
                'name' => 'Finance',
                'description' => 'Accounting, financial analysis, and bookkeeping',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('job_categories')->insert([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'is_active' => $category['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

class JobLocationSeeder extends Seeder
{
    public function run()
    {
        $locations = [
            [
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'postal_code' => '10001',
                'is_remote' => false,
            ],
            [
                'city' => 'San Francisco',
                'state' => 'CA',
                'country' => 'USA',
                'postal_code' => '94105',
                'is_remote' => false,
            ],
            [
                'city' => 'Austin',
                'state' => 'TX',
                'country' => 'USA',
                'postal_code' => '78701',
                'is_remote' => false,
            ],
            [
                'city' => 'London',
                'state' => null,
                'country' => 'UK',
                'postal_code' => 'EC1A 1BB',
                'is_remote' => false,
            ],
            [
                'city' => 'Remote',
                'state' => null,
                'country' => 'Worldwide',
                'postal_code' => null,
                'is_remote' => true,
            ],
        ];

        foreach ($locations as $location) {
            DB::table('job_locations')->insert([
                'city' => $location['city'],
                'state' => $location['state'],
                'country' => $location['country'],
                'postal_code' => $location['postal_code'],
                'is_remote' => $location['is_remote'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

class JobSkillSeeder extends Seeder
{
    public function run()
    {
        $skills = [
            'PHP', 'Laravel', 'JavaScript', 'React', 'Vue.js', 'Angular', 'Node.js',
            'Python', 'Django', 'Ruby', 'Ruby on Rails', 'Java', 'Spring Boot',
            'C#', '.NET', 'SQL', 'MySQL', 'PostgreSQL', 'MongoDB', 'Redis',
            'AWS', 'Azure', 'GCP', 'Docker', 'Kubernetes', 'CI/CD', 'Git',
            'HTML', 'CSS', 'Sass', 'TypeScript', 'GraphQL', 'REST API',
            'Figma', 'Adobe XD', 'Photoshop', 'Illustrator', 'UI Design', 'UX Design',
            'SEO', 'SEM', 'Content Marketing', 'Social Media', 'Email Marketing',
            'Project Management', 'Agile', 'Scrum', 'Jira', 'Confluence'
        ];

        foreach ($skills as $skill) {
            DB::table('job_posting_skill')->insert([
                'name' => $skill,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

class JobPostingSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $categories = DB::table('job_categories')->pluck('id');
        $locations = DB::table('job_locations')->pluck('id');
        $skills = DB::table('job_posting_skill')->pluck('id');
        
        $employmentTypes = ['Full-time', 'Part-time', 'Contract', 'Temporary', 'Internship'];
        $experienceLevels = ['Entry', 'Junior', 'Mid-level', 'Senior', 'Lead', 'Manager', 'Director'];
        $salaryPeriods = ['Hourly', 'Monthly', 'Annual'];
        
        // Create 15 job postings
        for ($i = 1; $i <= 15; $i++) {
            $title = $this->getRandomJobTitle();
            $categoryId = $categories->random();
            $locationId = $locations->random();
            $employmentType = $employmentTypes[array_rand($employmentTypes)];
            $experienceLevel = $experienceLevels[array_rand($experienceLevels)];
            $salaryPeriod = $salaryPeriods[array_rand($salaryPeriods)];
            
            $isPublished = rand(0, 10) > 2; // 80% chance of being published
            $isFeatured = rand(0, 10) > 7; // 30% chance of being featured
            
            $salaryMin = rand(30000, 80000);
            $salaryMax = $salaryMin + rand(10000, 40000);
            
            $publishedDate = $isPublished ? Carbon::now()->subDays(rand(1, 30)) : null;
            $deadlineDate = Carbon::now()->addDays(rand(7, 60));
            
            $jobId = DB::table('job_postings')->insertGetId([
                'title' => $title,
                'slug' => Str::slug($title) . '-' . Str::random(5),
                'category_id' => $categoryId,
                'location_id' => $locationId,
                'created_by' => $admin->id,
                'description' => $this->getJobDescription(),
                'requirements' => $this->getJobRequirements(),
                'benefits' => $this->getJobBenefits(),
                'responsibilities' => $this->getJobResponsibilities(),
                'employment_type' => $employmentType,
                'experience_level' => $experienceLevel,
                'salary_min' => $salaryMin,
                'salary_max' => $salaryMax,
                'salary_currency' => 'USD',
                'salary_period' => $salaryPeriod,
                'application_deadline' => $deadlineDate,
                'is_featured' => $isFeatured,
                'is_published' => $isPublished,
                'published_at' => $publishedDate,
                'views_count' => rand(0, 500),
                'applications_count' => rand(0, 20),
                'created_at' => Carbon::now()->subDays(rand(1, 60)),
                'updated_at' => Carbon::now(),
            ]);

        }
    }
    
    private function getRandomJobTitle()
    {
        $roles = [
            'Software Engineer', 'Full Stack Developer', 'Frontend Developer',
            'Backend Developer', 'UI/UX Designer', 'Product Manager',
            'DevOps Engineer', 'Data Scientist', 'Marketing Manager',
            'Content Writer', 'Sales Representative', 'Customer Support Specialist',
            'HR Manager', 'Financial Analyst', 'Project Manager'
        ];
        
        $prefixes = ['Senior', 'Junior', 'Lead', 'Principal', '', ''];
        
        $prefix = $prefixes[array_rand($prefixes)];
        $role = $roles[array_rand($roles)];
        
        return $prefix ? "$prefix $role" : $role;
    }
    
    private function getJobDescription()
    {
        return "We are looking for a talented professional to join our dynamic team. 
        This role offers an exciting opportunity to work on cutting-edge projects
        with a diverse team of experts. You'll have the chance to grow your skills
        and make a significant impact within our organization. Our company culture
        promotes innovation, collaboration, and continuous learning.";
    }
    
    private function getJobRequirements()
    {
        return "• Bachelor's degree in relevant field or equivalent practical experience
        • 3+ years of relevant industry experience
        • Strong communication and collaboration skills
        • Ability to work in a fast-paced environment
        • Problem-solving mindset and attention to detail
        • Willingness to learn new technologies and adapt to changing requirements";
    }
    
    private function getJobBenefits()
    {
        return "• Competitive salary and performance bonuses
        • Health, dental, and vision insurance
        • 401(k) matching
        • Flexible working hours and remote work options
        • Professional development opportunities
        • Generous paid time off
        • Modern office with amenities";
    }
    
    private function getJobResponsibilities()
    {
        return "• Collaborate with cross-functional teams to deliver high-quality solutions
        • Participate in planning and estimation sessions
        • Design, develop, and maintain key features and functionality
        • Troubleshoot and resolve issues efficiently
        • Stay current with industry trends and best practices
        • Mentor junior team members when appropriate
        • Contribute to process improvements and innovation";
    }
}

class JobApplicationSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('email', '!=', 'admin@example.com')->pluck('id');
        $admin = User::where('email', 'admin@example.com')->first();
        $jobPostings = DB::table('job_postings')->where('is_published', true)->pluck('id');
        
        $statuses = [
            'submitted', 'under_review', 'interview_scheduled', 
            'interviewed', 'shortlisted', 'rejected', 
            'offer_made', 'offer_accepted', 'offer_declined', 'hired'
        ];
        
        // Seed 30 job applications
        for ($i = 1; $i <= 30; $i++) {
            $jobId = $jobPostings->random();
            $userId = rand(0, 5) > 0 ? $users->random() : null; // 5/6 chance of being from a registered user
            $status = $statuses[array_rand($statuses)];
            
            $firstName = $this->getRandomFirstName();
            $lastName = $this->getRandomLastName();
            $email = $userId ? User::find($userId)->email : strtolower($firstName . '.' . $lastName . '@example.com');
            
            $createdAt = Carbon::now()->subDays(rand(1, 30));
            $reviewedAt = in_array($status, ['submitted']) ? null : $createdAt->copy()->addDays(rand(1, 5));
            
            DB::table('job_applications')->insert([
                'job_posting_id' => $jobId,
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => '+1' . rand(200, 999) . rand(100, 999) . rand(1000, 9999),
                'cover_letter' => $this->getCoverLetter(),
                'resume_path' => 'resumes/' . Str::slug("$firstName-$lastName") . '-' . Str::random(8) . '.pdf',
                'portfolio_url' => rand(0, 1) ? 'https://portfolio.' . Str::slug($firstName) . '.com' : null,
                'linkedin_url' => rand(0, 1) ? 'https://linkedin.com/in/' . Str::slug("$firstName-$lastName") : null,
                'github_url' => rand(0, 1) ? 'https://github.com/' . Str::slug($firstName) : null,
                'additional_information' => rand(0, 1) ? $this->getAdditionalInfo() : null,
                'skills' => $this->getRandomSkillsList(),
                'current_company' => rand(0, 1) ? $this->getRandomCompany() : null,
                'current_position' => rand(0, 1) ? $this->getRandomPosition() : null,
                'education' => $this->getRandomEducation(),
                'highest_degree' => $this->getRandomDegree(),
                'expected_salary' => rand(40000, 120000),
                'years_of_experience' => rand(0, 15),
                'referral_source' => $this->getRandomReferralSource(),
                'status' => $status,
                'admin_notes' => in_array($status, ['submitted']) ? null : $this->getRandomAdminNotes(),
                'reviewed_at' => $reviewedAt,
                'reviewed_by' => $reviewedAt ? $admin->id : null,
                'created_at' => $createdAt,
                'updated_at' => Carbon::now(),
            ]);
            
            // Update the applications count for this job
            DB::table('job_postings')
                ->where('id', $jobId)
                ->increment('applications_count');
        }
    }
    
    private function getRandomFirstName()
    {
        $names = ['Alex', 'Jordan', 'Taylor', 'Morgan', 'Casey', 'Riley', 'Avery', 'Quinn', 
                 'Sam', 'Jamie', 'Blake', 'Cameron', 'Emerson', 'Finley', 'Hayden', 'Dakota',
                 'James', 'John', 'Robert', 'Michael', 'William', 'David', 'Mary', 'Patricia',
                 'Linda', 'Elizabeth', 'Jennifer', 'Maria', 'Susan', 'Margaret', 'Emily'];
        
        return $names[array_rand($names)];
    }
    
    private function getRandomLastName()
    {
        $names = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
                 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
                 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
                 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker'];
        
        return $names[array_rand($names)];
    }
    
    private function getCoverLetter()
    {
        return "Dear Hiring Manager,

I am writing to express my interest in the position at your company. With my skills and experience, I believe I would be a valuable addition to your team.

Throughout my career, I have developed expertise in various areas relevant to this role. I am particularly skilled at problem-solving, communication, and collaboration, which I believe are essential for success in this position.

I am impressed by your company's reputation and the innovative work you're doing. I am excited about the opportunity to contribute to your continued success.

Thank you for considering my application. I look forward to the possibility of discussing my qualifications further.

Sincerely,
[Applicant Name]";
    }
    
    private function getAdditionalInfo()
    {
        $infos = [
            "I'm available to start immediately.",
            "I'm currently relocating to your area.",
            "I'm particularly interested in roles that offer mentorship opportunities.",
            "I'm pursuing additional certifications in my field.",
            "I've been following your company for several years and admire your work."
        ];
        
        return $infos[array_rand($infos)];
    }
    
    private function getRandomSkillsList()
    {
        $allSkills = DB::table('job_posting_skill')->pluck('name')->toArray();
        $selectedSkills = array_rand(array_flip($allSkills), rand(3, 8));
        
        return implode(', ', (array)$selectedSkills);
    }
    
    private function getRandomCompany()
    {
        $companies = [
            'Acme Inc.', 'TechCorp', 'Innovate Solutions', 'Digital Dynamics',
            'Global Systems', 'NextGen Tech', 'SoftServe', 'Data Innovations',
            'Creative Designs', 'Cloud Networks', 'Smart Analytics', 'Modern Solutions'
        ];
        
        return $companies[array_rand($companies)];
    }
    
    private function getRandomPosition()
    {
        $positions = [
            'Software Developer', 'System Analyst', 'Project Manager', 'UX Designer',
            'Data Scientist', 'Network Engineer', 'Marketing Specialist', 'Content Writer',
            'Product Manager', 'Sales Representative', 'Customer Support', 'HR Specialist'
        ];
        
        return $positions[array_rand($positions)];
    }
    
    private function getRandomEducation()
    {
        $universities = [
            'University of California', 'Stanford University', 'Massachusetts Institute of Technology',
            'Harvard University', 'New York University', 'University of Texas', 'University of Michigan',
            'Cornell University', 'University of Washington', 'Georgia Tech', 'State University'
        ];
        
        return $universities[array_rand($universities)];
    }
    
    private function getRandomDegree()
    {
        $degrees = [
            "Bachelor's in Computer Science", "Master's in Business Administration",
            "Bachelor's in Engineering", "Master's in Computer Science",
            "Bachelor's in Marketing", "Bachelor's in Design",
            "Associate's in Web Development", "Ph.D. in Computer Science",
            "Bachelor's in Finance", "Master's in Data Science"
        ];
        
        return $degrees[array_rand($degrees)];
    }
    
    private function getRandomReferralSource()
    {
        $sources = [
            'LinkedIn', 'Indeed', 'Company Website', 'Job Fair',
            'Employee Referral', 'University Career Center', 'Glassdoor',
            'Professional Network', 'Social Media', 'Search Engine'
        ];
        
        return $sources[array_rand($sources)];
    }
    
    private function getRandomAdminNotes()
    {
        $notes = [
            "Strong technical skills, good fit for the team.",
            "Great communication skills, but limited technical experience.",
            "Impressive portfolio, should move forward in the process.",
            "Good candidate, but salary expectations may be too high.",
            "Consider for different role in the organization.",
            "Strong academic background but limited practical experience.",
            "Cultural fit seems excellent, technical assessment pending.",
            "Potential red flag: frequent job changes.",
            "Excellent problem-solving skills demonstrated in interview.",
            "Follow up on references, experience seems inconsistent."
        ];
        
        return $notes[array_rand($notes)];
    }
}