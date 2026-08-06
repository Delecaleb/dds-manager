<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('od_patients')) {
            Schema::table('od_patients', function (Blueprint $table) {
                $table->text('AddrNote')->nullable()->change();
                $table->text('FamFinUrgNote')->nullable()->change();
                $table->text('MedUrgNote')->nullable()->change();
                $table->text('ApptModNote')->nullable()->change();
                $table->text('EmploymentNote')->nullable()->change();
            });
        }

        if (Schema::hasTable('od_procedure_logs')) {
            Schema::table('od_procedure_logs', function (Blueprint $table) {
                $table->text('ClaimNote')->nullable()->change();
                $table->text('BillingNote')->nullable()->change();
            });
        }

        if (Schema::hasTable('claim_procs')) {
            Schema::table('claim_procs', function (Blueprint $table) {
                $table->text('EstimateNote')->nullable()->change();
            });
        }

        if (Schema::hasTable('od_claim_procs')) {
            Schema::table('od_claim_procs', function (Blueprint $table) {
                if (Schema::hasColumn('od_claim_procs', 'EstimateNote')) {
                    $table->text('EstimateNote')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('od_providers')) {
            Schema::table('od_providers', function (Blueprint $table) {
                $table->text('SchedNote')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('od_patients')) {
            Schema::table('od_patients', function (Blueprint $table) {
                $table->string('AddrNote')->nullable()->change();
                $table->string('FamFinUrgNote')->nullable()->change();
                $table->string('MedUrgNote')->nullable()->change();
                $table->string('ApptModNote')->nullable()->change();
                $table->string('EmploymentNote')->nullable()->change();
            });
        }

        if (Schema::hasTable('od_procedure_logs')) {
            Schema::table('od_procedure_logs', function (Blueprint $table) {
                $table->string('ClaimNote')->nullable()->change();
                $table->string('BillingNote')->nullable()->change();
            });
        }

        if (Schema::hasTable('claim_procs')) {
            Schema::table('claim_procs', function (Blueprint $table) {
                $table->string('EstimateNote')->nullable()->change();
            });
        }

        if (Schema::hasTable('od_providers')) {
            Schema::table('od_providers', function (Blueprint $table) {
                $table->string('SchedNote')->nullable()->change();
            });
        }
    }
};
