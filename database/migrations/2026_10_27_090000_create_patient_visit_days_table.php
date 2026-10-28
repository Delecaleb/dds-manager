<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Summary tables (speed plan Phase 4).
 *
 * `patient_visit_days`: one row per office × clinic × patient × provider × day that has a
 * completed procedure, with flags for the procedure conditions the patient-history KPIs
 * filter on. It replaces full-history scans of od_procedure_logs (≈2.7× fewer rows, all
 * typed, small enough to stay in memory). Built by App\Domain\Patient\PatientVisitDays.
 *
 * `summary_states`: whether an office's summary is ready to read. Anything not `ready`
 * makes the services fall back to the raw tables, so a missing or stale summary is slow,
 * never wrong.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('patient_visit_days')) {
            Schema::create('patient_visit_days', function (Blueprint $table) {
                $table->unsignedBigInteger('office_id');
                $table->bigInteger('clinic_num');
                $table->bigInteger('pat_num');
                $table->bigInteger('prov_num');
                $table->date('visit_date');
                // Any row with ProcStatus in ProcStatus::completed().
                $table->boolean('completed')->default(false);
                // ... that joins to od_procedures (the KPI queries inner-join it).
                $table->boolean('coded')->default(false);
                // ... coded, and ProcCode not a broken-appointment code.
                $table->boolean('coded_not_broken')->default(false);
                $table->boolean('fmx')->default(false);
                $table->boolean('recare')->default(false);
                // Legacy new-patient status list (2, '2', 'C'), with / without broken CodeNums.
                $table->boolean('np_any')->default(false);
                $table->boolean('np_not_broken')->default(false);

                $table->primary(['office_id', 'clinic_num', 'pat_num', 'visit_date', 'prov_num'], 'patient_visit_days_primary');
                $table->index(['office_id', 'pat_num', 'visit_date'], 'patient_visit_days_office_pat_date');
            });
        }

        if (! Schema::hasTable('summary_states')) {
            Schema::create('summary_states', function (Blueprint $table) {
                $table->string('summary', 64);
                $table->unsignedBigInteger('office_id');
                $table->string('status', 16);
                $table->timestamp('built_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->primary(['summary', 'office_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_visit_days');
        Schema::dropIfExists('summary_states');
    }
};
