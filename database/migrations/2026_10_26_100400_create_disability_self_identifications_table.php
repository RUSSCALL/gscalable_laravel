<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kept apart from job_applications so reviewers never see it; only reports count it.
        Schema::create('disability_self_identifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disability_self_identifications');
    }
};
