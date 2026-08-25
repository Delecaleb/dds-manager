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
        Schema::table('od_appointments', function (Blueprint $table) {
            $table->index('PatNum', 'od_appointments_patnum_index');
            $table->index(['PatNum', 'AptDateTime'], 'od_appointments_patnum_aptdate_index');
        });

        Schema::table('od_claim_procs', function (Blueprint $table) {
            $table->index('ProcDate', 'od_claim_procs_procdate_index');
        });

        Schema::table('od_adjustments', function (Blueprint $table) {
            $table->index('AdjDate', 'od_adjustments_adjdate_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('od_appointments', function (Blueprint $table) {
            $table->dropIndex('od_appointments_patnum_index');
            $table->dropIndex('od_appointments_patnum_aptdate_index');
        });

        Schema::table('od_claim_procs', function (Blueprint $table) {
            $table->dropIndex('od_claim_procs_procdate_index');
        });

        Schema::table('od_adjustments', function (Blueprint $table) {
            $table->dropIndex('od_adjustments_adjdate_index');
        });
    }
};
