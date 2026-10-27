<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_screener_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->string('importance', 20);
            $table->boolean('required_answer');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        // Answers keep a copy of the question so a later edit to the job never changes an existing rank.
        Schema::create('job_application_screener_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            // Default constraint name exceeds MySQL's 64-character limit.
            $table->foreignId('job_screener_question_id')->nullable()->constrained(indexName: 'screener_answers_question_fk')->nullOnDelete();
            $table->string('question');
            $table->string('importance', 20);
            $table->boolean('required_answer');
            $table->boolean('answer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_application_screener_answers');
        Schema::dropIfExists('job_screener_questions');
    }
};
