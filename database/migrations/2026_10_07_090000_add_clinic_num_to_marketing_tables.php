<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Growth Engine rows belong to a reporting location, exactly as the rest of the app
 * defines one (ClinicRegistry): an office, or one clinic of a multi-clinic office.
 *
 * office_id already existed; clinic_num completes the key. NULL clinic_num on a
 * multi-clinic office means "the office as a whole": the row counts for every clinic.
 */
return new class extends Migration
{
    private const TABLES = ['marketing_sites', 'marketing_ad_accounts', 'marketing_ad_campaigns'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'clinic_num')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedInteger('clinic_num')->nullable()->after('office_id');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'clinic_num')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('clinic_num');
                });
            }
        }
    }
};
