<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BaseQuerySyncService used to ALTER these tables at runtime when office_id
     * was missing. DDL during concurrent syncs causes metadata-lock stalls, so
     * the guarantee now lives here. No-op on databases that already have it.
     *
     * @var list<string>
     */
    private array $tables = [
        'od_adjustments',
        'od_appointments',
        'od_carriers',
        'od_claim_payments',
        'od_claim_procs',
        'od_clinics',
        'od_definitions',
        'od_deposits',
        'od_histappointments',
        'od_insplans',
        'od_patients',
        'od_pay_plan_charges',
        'od_pay_splits',
        'od_payments',
        'od_procedure_logs',
        'od_procedures',
        'od_providers',
        'od_recall_types',
        'od_recalls',
        'od_schedules',
        'od_statements',
        'od_treatment_plan_attachments',
        'treatment_plans',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'office_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('office_id')->default(1)->index();
            });
        }
    }

    public function down(): void
    {
        // Intentionally irreversible: office_id is required by every sync.
    }
};
