<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A row exists only for a ticked checklist item.
        Schema::create('onboarding_task_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->string('task', 50);
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['job_application_id', 'task']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_task_completions');
    }
};
