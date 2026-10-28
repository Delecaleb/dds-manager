<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * patient_visit_days, step 4.C: the flag behind PatientVisitService (patient visits and the
 * new-patient lookup): a ProcStatus::completed() procedure whose CodeNum is not one of the
 * office's broken-appointment CodeNums. The date index serves its date-range counts.
 *
 * Only touches the summary table. Rows built before this flag existed read 0, so the
 * office must be rebuilt (`php artisan summaries:rebuild`) before PatientVisitService
 * switches to it; summary_states is reset to stale here so that happens safely.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('patient_visit_days', 'completed_not_broken')) {
            Schema::table('patient_visit_days', function (Blueprint $table) {
                $table->boolean('completed_not_broken')->default(false)->after('completed');
                $table->index(['office_id', 'visit_date', 'completed_not_broken', 'pat_num'], 'patient_visit_days_office_date');
            });
        }

        DB::table('summary_states')->where('summary', 'patient_visit_days')->update(['status' => 'stale']);
    }

    public function down(): void
    {
        Schema::table('patient_visit_days', function (Blueprint $table) {
            $table->dropIndex('patient_visit_days_office_date');
            $table->dropColumn('completed_not_broken');
        });
    }
};
