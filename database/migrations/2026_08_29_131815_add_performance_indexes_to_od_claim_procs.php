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
            $table->index('PatNum', 'od_claim_procs_patnum_index');
            $table->index('PlanNum', 'od_claim_procs_plannum_index');
            $table->index('DateCP', 'od_claim_procs_datecp_index');
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
            $table->dropIndex('od_claim_procs_patnum_index');
            $table->dropIndex('od_claim_procs_plannum_index');
            $table->dropIndex('od_claim_procs_datecp_index');
        });
    }
};
