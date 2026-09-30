<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_changes', function (Blueprint $table) {
            $table->id();

            // Emails are snapshotted so the history still reads correctly after
            // an account is deleted and its id is nulled.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_email');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('changed_by_email')->nullable();

            // Role names rather than ids, so the record survives role table changes.
            $table->string('from_role')->nullable();
            $table->string('to_role');

            // "web" for the roles page, "console" for the artisan command.
            $table->string('source', 20);
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_changes');
    }
};
