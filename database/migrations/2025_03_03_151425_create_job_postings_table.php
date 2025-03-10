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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 100)->unique();
            $table->foreignId('category_id')->nullable()->constrained('job_categories')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('job_locations')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->text('description');
            $table->text('requirements');
            $table->text('benefits')->nullable();
            $table->text('responsibilities');
            $table->string('employment_type', 50); // Full-time, Part-time, Contract, etc.
            $table->string('experience_level', 50); // Entry, Mid, Senior, etc.
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('salary_currency', 10)->default('USD');
            $table->string('salary_period', 20)->nullable(); // Hourly, Monthly, Annual
            $table->date('application_deadline');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('applications_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
