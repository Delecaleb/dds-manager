<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * od_appointments: an index for "this patient's appointment history at this office"
 * (NewPatientsScheduledService, first-visit checks). The existing indexes lead with PatNum
 * alone or with office + date, so those lookups read every row of each patient across
 * offices and filtered status / new-patient flag afterwards (0.7–0.9 s per office).
 * Covers the filter columns, so the lookup is answered from the index.
 */
return new class extends Migration
{
    private const NAME = 'idx_od_appts_office_pat_status_date';

    public function up(): void
    {
        if (Schema::hasIndex('od_appointments', self::NAME)) {
            return;
        }

        Schema::table('od_appointments', function (Blueprint $table) {
            $table->index(['office_id', 'PatNum', 'AptStatus', 'AptDateTime', 'IsNewPatient'], self::NAME);
        });
    }

    public function down(): void
    {
        Schema::table('od_appointments', function (Blueprint $table) {
            $table->dropIndex(self::NAME);
        });
    }
};
