<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Applicants are given a reference number on the confirmation page and in the
 * confirmation email. Using the auto-increment id for that leaks the total
 * application volume to anyone who applies twice, so each application gets an
 * opaque random reference instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('reference', 32)->nullable()->after('id');
        });

        // Backfill existing rows before adding the unique index.
        DB::table('job_applications')->orderBy('id')->select('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('job_applications')
                    ->where('id', $row->id)
                    ->update(['reference' => static::generateReference()]);
            }
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->unique('reference');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->dropColumn('reference');
        });
    }

    /**
     * Mirrors JobApplication::generateReference(). Duplicated here on purpose:
     * a migration must keep working even if the model changes later.
     */
    public static function generateReference(): string
    {
        // Ambiguous characters (0/O, 1/I) excluded so references survive being
        // read aloud or copied off a screen.
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

        do {
            $token = '';
            for ($i = 0; $i < 8; $i++) {
                $token .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $reference = 'GST-' . substr($token, 0, 4) . '-' . substr($token, 4, 4);
        } while (DB::table('job_applications')->where('reference', $reference)->exists());

        return $reference;
    }
};
