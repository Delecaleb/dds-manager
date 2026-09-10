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
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('od_claim_procs', function (Blueprint $table) {
            $table->index(['office_id', 'ClaimPaymentNum'], 'od_claim_procs_office_claimpaymentnum_index');
        });

        Schema::table('od_claim_payments', function (Blueprint $table) {
            $table->index(['office_id', 'ClaimPaymentNum'], 'od_claim_payments_office_claimpaymentnum_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('od_claim_procs', function (Blueprint $table) {
            $table->dropIndex('od_claim_procs_office_claimpaymentnum_index');
        });

        Schema::table('od_claim_payments', function (Blueprint $table) {
            $table->dropIndex('od_claim_payments_office_claimpaymentnum_index');
        });
    }
};
