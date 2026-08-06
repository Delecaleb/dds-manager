<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    protected array $tables = [
        'od_patients',
        'od_appointments',
        'od_procedure_logs',
        'od_procedures',
        'od_adjustments',
        'od_carriers',
        'od_claim_payments',
        'od_clinics',
        'od_definitions',
        'od_deposits',
        'od_insplans',
        'od_patient_balances',
        'od_pay_plan_charges',
        'od_payments',
        'od_providers',
        'od_recalls',
        'od_recall_types',
        'od_schedule',
        'od_treatment_plan_attachments',
        'claim_procs',
        'pay_splits',
        'sync_logs',
        'treatment_plans',
        'account_modules',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'office_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('office_id')->default(1)->index();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'office_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('office_id');
                });
            }
        }
    }
};
