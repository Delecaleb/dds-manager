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
        if (! Schema::hasTable('provider_configurations')) {
            Schema::create('provider_configurations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id')->index();
                $table->unsignedBigInteger('clinic_num')->nullable()->index();
                $table->unsignedBigInteger('prov_num')->index();
                $table->boolean('is_visible')->default(true);
                $table->string('specialty', 50)->nullable();
                $table->timestamps();

                $table->unique(['office_id', 'clinic_num', 'prov_num'], 'prov_config_office_clinic_prov_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_configurations');
    }
};
