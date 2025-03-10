<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('resume_path'); // File path to uploaded resume
            $table->string('portfolio_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->text('additional_information')->nullable();
            $table->text('skills')->nullable();
            $table->string('current_company')->nullable();
            $table->string('current_position')->nullable();
            $table->string('education')->nullable();
            $table->string('highest_degree')->nullable();
            $table->decimal('expected_salary', 10, 2)->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->string('referral_source')->nullable();
            $table->enum('status', [
                'submitted', 
                'under_review', 
                'interview_scheduled', 
                'interviewed', 
                'shortlisted', 
                'rejected', 
                'offer_made', 
                'offer_accepted', 
                'offer_declined',
                'hired'
            ])->default('submitted');
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
