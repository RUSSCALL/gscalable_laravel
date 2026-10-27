<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            // Stored exactly as the admin typed it; the zone travels alongside for display.
            $table->dateTime('scheduled_at');
            $table->string('timezone', 10)->default('ET');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('type', 20);
            $table->string('location')->nullable();
            $table->string('interviewers')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
