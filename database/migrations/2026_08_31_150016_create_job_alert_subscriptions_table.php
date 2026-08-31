<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_alert_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->foreignId('category_id')->nullable()
                ->constrained('job_categories')->nullOnDelete();
            $table->foreignId('location_id')->nullable()
                ->constrained('job_locations')->nullOnDelete();
            $table->string('keywords')->nullable();

            $table->boolean('is_confirmed')->default(false);
            $table->timestamp('confirmed_at')->nullable();

            // Nulled once used, so a spent confirmation link cannot be replayed.
            $table->string('confirmation_token', 64)->nullable()->unique();
            $table->string('unsubscribe_token', 64)->unique();

            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            // One subscription per email per filter combination. MySQL treats
            // NULLs as distinct in a unique index, so rows with no category and
            // no location are not covered here -- the controller de-duplicates
            // those explicitly before inserting.
            $table->unique(['email', 'category_id', 'location_id'], 'job_alert_unique_target');
            $table->index('is_confirmed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_alert_subscriptions');
    }
};
