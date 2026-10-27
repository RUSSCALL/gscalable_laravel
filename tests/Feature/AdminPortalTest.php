<?php

namespace Tests\Feature;

use App\Mail\ApplicantMessageMail;
use App\Mail\InterviewScheduled;
use App\Models\ApplicantMessage;
use App\Models\DisabilitySelfIdentification;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobApplicationAttachment;
use App\Models\JobApplicationScreenerAnswer;
use App\Models\JobPosting;
use App\Models\JobScreenerQuestion;
use App\Models\OnboardingTaskCompletion;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewApplicationReceived;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The 2026-10 client revisions: the admin pages behind the dead buttons, the new
 * application fields, screener ranking and private file storage. Runs against the
 * seeded database (MyISAM, no cascades), so tearDown removes every row it made.
 */
class AdminPortalTest extends TestCase
{
    private array $userIds = [];

    private array $jobIds = [];

    protected function tearDown(): void
    {
        $applicationIds = JobApplication::whereIn('job_posting_id', $this->jobIds)->pluck('id');

        foreach ([JobApplicationAttachment::class, JobApplicationScreenerAnswer::class, DisabilitySelfIdentification::class,
            Interview::class, ApplicantMessage::class, OnboardingTaskCompletion::class] as $model) {
            $model::whereIn('job_application_id', $applicationIds)->delete();
        }

        JobApplication::whereIn('id', $applicationIds)->delete();
        JobScreenerQuestion::whereIn('job_posting_id', $this->jobIds)->delete();
        JobPosting::whereIn('id', $this->jobIds)->orWhereIn('created_by', $this->userIds)->delete();
        DB::table('notifications')->where('notifiable_type', User::class)->whereIn('notifiable_id', $this->userIds)->delete();
        User::whereIn('id', $this->userIds)->delete();

        parent::tearDown();
    }

    private function makeUser(string $role): User
    {
        $user = new User();
        $user->name = 'Test ' . $role;
        $user->email = 'portal-' . bin2hex(random_bytes(4)) . '@example.test';
        $user->password = Hash::make('a-sufficiently-long-password');
        $user->role_id = Role::idFor($role);
        $user->email_verified_at = now();
        $user->save();
        $this->userIds[] = $user->id;

        return $user;
    }

    private function admin(): User
    {
        return $this->makeUser(Role::ADMIN);
    }

    /** @param array<int, array{0: string, 1: string, 2: bool}> $questions [question, importance, required answer] */
    private function makeJob(array $questions = [], array $attributes = []): JobPosting
    {
        $job = new JobPosting(array_merge([
            'title' => 'Portal Test Role ' . bin2hex(random_bytes(3)),
            'slug' => 'portal-test-' . bin2hex(random_bytes(4)),
            'description' => 'd',
            'requirements' => 'r',
            'responsibilities' => 'x',
            'employment_type' => 'Full-time',
            'experience_level' => 'Senior',
            'work_arrangement' => 'hybrid',
            'application_deadline' => now()->addMonth()->toDateString(),
            'is_published' => true,
            'published_at' => now(),
        ], $attributes));
        $job->created_by = $this->admin()->id;
        $job->save();
        $this->jobIds[] = $job->id;

        foreach ($questions as $position => [$question, $importance, $required]) {
            $job->screenerQuestions()->create([
                'question' => $question, 'importance' => $importance, 'required_answer' => $required, 'position' => $position,
            ]);
        }

        return $job;
    }

    private function applicationPayload(JobPosting $job, array $answers = [], array $overrides = []): array
    {
        $screener = [];
        foreach ($job->screenerQuestions as $i => $question) {
            $screener[$question->id] = $answers[$i] ? '1' : '0';
        }

        return array_merge([
            'first_name' => 'Morgan',
            'last_name' => 'Lee',
            'email' => 'portal-applicant-' . bin2hex(random_bytes(4)) . '@example.test',
            'phone' => '555-0199',
            'current_location' => 'Baltimore, MD',
            'education' => 'State University',
            'highest_degree' => "Bachelor's",
            'years_of_experience' => 7,
            'reason_for_leaving' => 'Contract ended.',
            'availability' => 'other',
            'availability_other' => 'After March 1',
            'work_authorization' => 'permanent_resident',
            'willing_to_relocate' => '1',
            'disability_status' => 'declined',
            'screener' => $screener,
            'terms_agree' => '1',
            '_ts' => Crypt::encryptString(time() - 30),
            'resume_path' => UploadedFile::fake()->create('resume.pdf', 120, 'application/pdf'),
            'cover_letter_file' => UploadedFile::fake()->create('cover.docx', 40, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'attachments' => [
                UploadedFile::fake()->create('security-plus.pdf', 80, 'application/pdf'),
                UploadedFile::fake()->image('badge.png'),
            ],
        ], $overrides);
    }

    /** Essential met, one of two Preferred met: ranks "Fit". */
    private function submitFitApplication(): JobApplication
    {
        $job = $this->makeJob([
            ['Do you hold an active Secret clearance?', 'essential', true],
            ['Do you have AWS experience?', 'preferred', true],
            ['Do you need visa sponsorship?', 'preferred', false],
        ]);

        $this->post(route('careers.apply.store', $job->slug), $this->applicationPayload($job, [true, true, true]))
            ->assertRedirect(route('careers.applied', $job->slug));

        return JobApplication::where('job_posting_id', $job->id)->firstOrFail();
    }

    // ------------------------------------------------------------- applications

    public function test_an_application_is_stored_with_its_details_files_answers_and_rank(): void
    {
        Mail::fake();
        Notification::fake();
        Storage::fake(config('filesystems.uploads'));
        $admin = $this->admin();

        $application = $this->submitFitApplication();

        $this->assertSame('Baltimore, MD', $application->current_location);
        $this->assertSame('After March 1', $application->availability_label);
        $this->assertSame('Permanent Resident', $application->work_authorization_label);
        $this->assertTrue($application->willing_to_relocate);
        $this->assertSame('fit', $application->screener_rank);
        $this->assertCount(3, $application->screenerAnswers);
        $this->assertCount(2, $application->attachments);

        $disk = Storage::disk(config('filesystems.uploads'));
        $disk->assertExists([$application->resume_path, $application->cover_letter_path, ...$application->attachments->pluck('path')]);

        // Stored apart from the application, never as one of its columns.
        $this->assertSame('declined', DisabilitySelfIdentification::where('job_application_id', $application->id)->value('status'));
        $this->assertArrayNotHasKey('disability_status', $application->getAttributes());

        Notification::assertSentTo($admin, NewApplicationReceived::class);
    }

    public function test_the_rank_follows_the_agreed_rule(): void
    {
        $answers = fn (array $rows) => collect($rows)->map(fn ($row) => new JobApplicationScreenerAnswer([
            'importance' => $row[0], 'required_answer' => true, 'answer' => $row[1],
        ]));

        $this->assertNull(JobApplication::rankFor([]));
        $this->assertSame('not_fit', JobApplication::rankFor($answers([['essential', false], ['preferred', true]])));
        $this->assertSame('top_fit', JobApplication::rankFor($answers([['essential', true], ['preferred', true]])));
        $this->assertSame('top_fit', JobApplication::rankFor($answers([['essential', true]])));

        $twoEssentialFourPreferred = fn (int $preferredMet) => $answers(array_merge(
            [['essential', true], ['essential', true]],
            array_map(fn ($i) => ['preferred', $i < $preferredMet], range(0, 3)),
        ));
        $this->assertSame('fit', JobApplication::rankFor($twoEssentialFourPreferred(2)));
        $this->assertSame('suitable', JobApplication::rankFor($twoEssentialFourPreferred(1)));
    }

    // ------------------------------------------------------------- applicants pages

    public function test_admins_see_the_application_and_its_files_but_never_the_disability_answer(): void
    {
        Mail::fake();
        Notification::fake();
        Storage::fake(config('filesystems.uploads'));
        $application = $this->submitFitApplication();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.applicants.index', ['job' => $application->job_posting_id]))
            ->assertOk()
            ->assertSee('Morgan Lee');

        $this->actingAs($admin)->get(route('admin.applicants.show', $application))
            ->assertOk()
            ->assertSee('Baltimore, MD')
            ->assertSee('Do you hold an active Secret clearance?')
            ->assertSee('rank-fit', false)
            ->assertDontSee(DisabilitySelfIdentification::OPTIONS['declined']);

        $this->assertNotNull($application->fresh()->reviewed_at, 'Opening an application marks it reviewed.');

        $this->actingAs($admin)->get(route('admin.applicants.download', [$application, 'resume']))->assertDownload('resume-morgan-lee.pdf');
        $this->actingAs($admin)->get(route('admin.applicants.download', [$application, $application->attachments->first()->id]))->assertOk();

        // A file that's gone from storage sends the admin back with a message, not a 404.
        Storage::disk(config('filesystems.uploads'))->delete($application->resume_path);
        $this->actingAs($admin)->get(route('admin.applicants.download', [$application, 'resume']))
            ->assertRedirect(route('admin.applicants.show', $application))
            ->assertSessionHas('error', 'Resume is no longer available. Ask the candidate to send it again.');
    }

    public function test_only_admins_reach_applicants_and_their_files(): void
    {
        Mail::fake();
        Notification::fake();
        Storage::fake(config('filesystems.uploads'));
        $application = $this->submitFitApplication();
        $download = route('admin.applicants.download', [$application, 'resume']);

        $this->get(route('admin.applicants.index'))->assertRedirect(route('login'));
        $this->get($download)->assertRedirect(route('login'));

        $applicant = $this->makeUser(Role::APPLICANT);
        $this->actingAs($applicant)->get(route('admin.applicants.show', $application))->assertForbidden();
        $this->actingAs($applicant)->get($download)->assertForbidden();
    }

    // ------------------------------------------------------------- interviews and messages

    public function test_scheduling_an_interview_emails_the_candidate_and_moves_the_status(): void
    {
        Mail::fake();
        Notification::fake();
        Storage::fake(config('filesystems.uploads'));
        $application = $this->submitFitApplication();

        $this->actingAs($this->admin())->post(route('admin.interviews.store'), [
            'job_application_id' => $application->id,
            'scheduled_at' => now()->addDays(2)->setTime(10, 30)->format('Y-m-d\TH:i'),
            'timezone' => 'ET',
            'duration_minutes' => 45,
            'type' => 'video',
            'location' => 'https://meet.example.test/abc',
            'notify_candidate' => '1',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame('interview_scheduled', $application->fresh()->status);
        $this->assertSame(1, Interview::upcoming()->where('job_application_id', $application->id)->count());
        Mail::assertSent(InterviewScheduled::class, fn ($mail) => $mail->hasTo($application->email));
    }

    public function test_messaging_a_candidate_sends_the_email_and_logs_it(): void
    {
        Mail::fake();
        Notification::fake();
        Storage::fake(config('filesystems.uploads'));
        $application = $this->submitFitApplication();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.messages.store', $application), [
            'subject' => 'Next steps',
            'body' => "Thanks for applying.\nCould you send two references?",
        ])->assertRedirect(route('admin.applicants.show', $application) . '#messages');

        Mail::assertSent(ApplicantMessageMail::class, fn ($mail) => $mail->hasTo($application->email) && $mail->hasReplyTo($admin->email));
        $this->actingAs($admin)->get(route('admin.messages.index'))->assertOk()->assertSee('Next steps');
    }

    // ------------------------------------------------------------- notifications, profile and settings

    public function test_notifications_reach_the_bell_and_can_be_read_and_cleared(): void
    {
        $admin = $this->admin();
        $job = $this->makeJob();
        $application = JobApplication::create([
            'job_posting_id' => $job->id, 'first_name' => 'Riley', 'last_name' => 'Ng',
            'email' => 'portal-bell@example.test', 'resume_path' => 'resumes/none.pdf',
        ]);
        $admin->notify(new NewApplicationReceived($application));

        $this->actingAs($admin)->get(route('AdminDashboard'))
            ->assertOk()
            ->assertSee('Riley Ng applied for ' . $job->title);

        $notification = $admin->notifications()->firstOrFail();
        $this->actingAs($admin)->post(route('admin.notifications.open', $notification->id))
            ->assertRedirect(route('admin.applicants.show', $application));
        $this->assertSame(0, $admin->unreadNotifications()->count());

        $this->actingAs($admin)->delete(route('admin.notifications.clear'))->assertRedirect();
        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_admins_can_update_their_profile_and_notification_settings(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.profile.update'), ['name' => 'Renamed Admin', 'email' => $admin->email])
            ->assertSessionHasNoErrors();
        $this->assertSame('Renamed Admin', $admin->fresh()->name);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'notifications' => ['new_application' => ['mail' => '1']],
        ]);
        $admin->refresh();
        $this->assertTrue($admin->wantsNotification('new_application', 'mail'));
        $this->assertFalse($admin->wantsNotification('new_application', 'database'));
    }

    // ------------------------------------------------------------- dashboard, listings and the job form

    public function test_every_admin_page_and_dashboard_button_leads_somewhere_real(): void
    {
        $admin = $this->admin();

        foreach (['AdminDashboard', 'jobListings', 'jobs.create', 'admin.applicants.index', 'admin.messages.index',
            'admin.interviews.index', 'admin.onboarding.index', 'admin.reports.index', 'admin.settings',
            'admin.profile', 'admin.notifications.index'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }

        $dashboard = $this->actingAs($admin)->get(route('AdminDashboard'));
        foreach (['jobs.create', 'admin.interviews.index', 'admin.reports.index', 'admin.profile', 'admin.settings'] as $route) {
            $dashboard->assertSee('href="' . route($route), false);
        }
        // The old Quick Actions were <a href="#" class="btn ...">, and Recent Activity was hardcoded.
        $dashboard->assertDontSee('href="#" class="btn', false)->assertDontSee('Position closed for');

        $this->actingAs($admin)->get(route('admin.reports.index', ['export' => 'csv']))->assertDownload();
    }

    public function test_job_listings_filter_and_export(): void
    {
        $admin = $this->admin();
        $featured = $this->makeJob([], ['is_featured' => true]);
        $plain = $this->makeJob();

        $this->actingAs($admin)->get(route('jobListings', ['filter' => 'featured']))
            ->assertOk()
            ->assertSee($featured->title)
            ->assertDontSee($plain->title);

        $export = $this->actingAs($admin)->get(route('jobListings', ['filter' => 'featured', 'export' => 'csv']));
        $export->assertDownload();
        $this->assertStringContainsString($featured->title, $export->streamedContent());
        $this->assertStringNotContainsString($plain->title, $export->streamedContent());
    }

    public function test_screener_questions_and_work_arrangement_are_saved_with_a_job_and_asked_on_the_form(): void
    {
        $admin = $this->admin();
        $title = 'Portal Form Role ' . bin2hex(random_bytes(3));

        $this->actingAs($admin)->post(route('jobs.store'), [
            'title' => $title,
            'work_arrangement' => 'hybrid',
            'employment_type' => 'Full-time',
            'experience_level' => 'Senior',
            'application_deadline' => now()->addMonth()->toDateString(),
            'description' => 'd', 'requirements' => 'r', 'responsibilities' => 'x',
            'is_featured' => '0', 'is_published' => '1',
            'screener_questions' => [
                ['question' => 'Can you work on-site two days a week?', 'importance' => 'essential', 'required_answer' => '1'],
                ['question' => '', 'importance' => 'preferred', 'required_answer' => '1'],
                ['question' => 'Do you have a CISSP?', 'importance' => 'preferred', 'required_answer' => '1'],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect(route('jobListings'));

        $job = JobPosting::where('title', $title)->firstOrFail();
        $this->jobIds[] = $job->id;

        $this->assertSame('hybrid', $job->work_arrangement);
        $this->assertSame(['Can you work on-site two days a week?', 'Do you have a CISSP?'], $job->screenerQuestions->pluck('question')->all());

        $this->get(route('careers.apply', $job->slug))
            ->assertOk()
            ->assertSee('Can you work on-site two days a week?')
            ->assertSee('name="screener[' . $job->screenerQuestions->first()->id . ']"', false);
    }
}
