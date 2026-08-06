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
        Schema::table('od_patients', function (Blueprint $table) {
            $table->index('Guarantor');
        });

        Schema::table('od_procedure_logs', function (Blueprint $table) {
            $table->index('ProcNum');
            $table->index('PatNum');
            $table->index('ProcStatus');
            $table->index('ProcDate');
        });

        Schema::table('od_pay_splits', function (Blueprint $table) {
            $table->index('ProcNum');
            $table->index('PayPlanChargeNum');
            $table->index('PatNum');
        });

        Schema::table('od_claim_procs', function (Blueprint $table) {
            $table->index('ProcNum');
        });

        Schema::table('od_adjustments', function (Blueprint $table) {
            $table->index('ProcNum');
            $table->index('PatNum');
        });

        Schema::table('od_pay_plan_charges', function (Blueprint $table) {
            $table->index('Guarantor');
            $table->index('PayPlanChargeNum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('od_patients', function (Blueprint $table) {
            $table->dropIndex(['Guarantor']);
        });

        Schema::table('od_procedure_logs', function (Blueprint $table) {
            $table->dropIndex(['ProcNum']);
            $table->dropIndex(['PatNum']);
            $table->dropIndex(['ProcStatus']);
            $table->dropIndex(['ProcDate']);
        });

        Schema::table('od_pay_splits', function (Blueprint $table) {
            $table->dropIndex(['ProcNum']);
            $table->dropIndex(['PayPlanChargeNum']);
            $table->dropIndex(['PatNum']);
        });

        Schema::table('od_claim_procs', function (Blueprint $table) {
            $table->dropIndex(['ProcNum']);
        });

        Schema::table('od_adjustments', function (Blueprint $table) {
            $table->dropIndex(['ProcNum']);
            $table->dropIndex(['PatNum']);
        });

        Schema::table('od_pay_plan_charges', function (Blueprint $table) {
            $table->dropIndex(['Guarantor']);
            $table->dropIndex(['PayPlanChargeNum']);
        });
    }
};
