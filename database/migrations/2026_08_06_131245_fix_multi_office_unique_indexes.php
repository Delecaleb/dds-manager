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
            $table->dropUnique('od_patients_patnum_unique');
            $table->unique(['office_id', 'PatNum'], 'od_patients_office_patnum_unique');
        });

        Schema::table('od_providers', function (Blueprint $table) {
            $table->dropUnique('od_providers_provnum_unique');
            $table->unique(['office_id', 'ProvNum'], 'od_providers_office_provnum_unique');
        });

        Schema::table('od_patient_balances', function (Blueprint $table) {
            $table->dropUnique('od_patient_balances_patnum_unique');
            $table->unique(['office_id', 'PatNum'], 'od_patient_balances_office_patnum_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('od_patients', function (Blueprint $table) {
            $table->dropUnique('od_patients_office_patnum_unique');
            $table->unique('PatNum', 'od_patients_patnum_unique');
        });

        Schema::table('od_providers', function (Blueprint $table) {
            $table->dropUnique('od_providers_office_provnum_unique');
            $table->unique('ProvNum', 'od_providers_provnum_unique');
        });

        Schema::table('od_patient_balances', function (Blueprint $table) {
            $table->dropUnique('od_patient_balances_office_patnum_unique');
            $table->unique('PatNum', 'od_patient_balances_patnum_unique');
        });
    }
};
