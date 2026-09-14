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
        if (! Schema::hasTable('basic_settings')) {
            Schema::create('basic_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id')->nullable()->index();
                $table->unsignedBigInteger('clinic_num')->nullable()->index();

                // 1. Display Production Type
                $table->boolean('display_gross_production')->default(true);
                $table->boolean('display_net_production')->default(true);
                $table->boolean('display_adjustment')->default(true);

                // 2. Dashboard Visits Display
                $table->boolean('display_new_patient_tile')->default(true);
                $table->boolean('display_new_patient_graph')->default(true);
                $table->boolean('display_patient_visits_graph')->default(true);

                // 3. Front Office | Patient Portal Option
                $table->boolean('front_office_inactive_patients')->default(true);

                // 4. Production-Type-Based Metrics
                $table->string('collection_rate_metric', 20)->default('net'); // 'net' or 'gross'

                $table->timestamps();

                $table->unique(['office_id', 'clinic_num'], 'basic_settings_office_clinic_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_settings');
    }
};
