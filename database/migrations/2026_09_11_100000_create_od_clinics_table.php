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
        if (! Schema::hasTable('od_clinics')) {
            Schema::create('od_clinics', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id')->default(1)->index();
                $table->bigInteger('ClinicNum')->index();
                $table->string('Description', 255)->nullable();
                $table->string('Abbr', 50)->nullable();
                $table->string('Phone', 30)->nullable();
                $table->string('Fax', 30)->nullable();
                $table->string('Address', 255)->nullable();
                $table->string('Address2', 255)->nullable();
                $table->string('City', 255)->nullable();
                $table->string('State', 255)->nullable();
                $table->string('Zip', 255)->nullable();
                $table->boolean('IsHidden')->default(false);
                $table->integer('ItemOrder')->nullable()->default(0);
                $table->timestamps();

                $table->unique(['office_id', 'ClinicNum'], 'od_clinics_office_clinic_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_clinics');
    }
};
