<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('cover_letter_path')->nullable()->after('cover_letter');
            $table->string('current_location', 191)->nullable()->after('phone');
            $table->text('reason_for_leaving')->nullable()->after('current_position');
            $table->string('availability', 20)->nullable()->after('reason_for_leaving');
            $table->string('availability_other', 191)->nullable()->after('availability');
            $table->string('work_authorization', 30)->nullable()->after('availability_other');
            $table->boolean('willing_to_relocate')->nullable()->after('work_authorization');
            $table->string('screener_rank', 20)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn([
                'cover_letter_path', 'current_location', 'reason_for_leaving', 'availability',
                'availability_other', 'work_authorization', 'willing_to_relocate', 'screener_rank',
            ]);
        });
    }
};
