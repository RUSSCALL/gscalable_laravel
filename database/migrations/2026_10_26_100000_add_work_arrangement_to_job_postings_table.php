<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->string('work_arrangement', 20)->default('on_site')->after('location_id');
            $table->timestamp('expiry_notified_at')->nullable()->after('published_at');
        });

        // Until now "remote" lived only on the location.
        DB::table('job_postings')
            ->whereIn('location_id', DB::table('job_locations')->where('is_remote', true)->select('id'))
            ->update(['work_arrangement' => 'remote']);
    }

    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn(['work_arrangement', 'expiry_notified_at']);
        });
    }
};
