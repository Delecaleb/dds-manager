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
        Schema::create('od_carriers', function (Blueprint $table) {
            $table->id();
            $table->integer('CarrierNum')->nullable();

            $table->string('CarrierName')->nullable();

            $table->string('Address')->nullable();

            $table->string('Address2')->nullable();

            $table->string('City')->nullable();

            $table->string('State')->nullable();

            $table->string('Zip')->nullable();

            $table->string('Phone')->nullable();

            $table->string('ElectID')->nullable();

            $table->integer('NoSendElect')->nullable();

            $table->integer('IsCDA')->nullable();

            $table->string('CDAnetVersion')->nullable();

            $table->integer('CanadianNetworkNum')->nullable();

            $table->integer('IsHidden')->nullable();

            $table->integer('CanadianEncryptionMethod')->nullable();

            $table->integer('CanadianSupportedTypes')->nullable();

            $table->integer('SecUserNumEntry')->nullable();

            $table->date('SecDateEntry')->nullable();

            $table->string('SecDateTEdit')->nullable();

            $table->string('TIN')->nullable();

            $table->integer('CarrierGroupName')->nullable();

            $table->integer('ApptTextBackColor')->nullable();

            $table->integer('IsCoinsuranceInverted')->nullable();

            $table->integer('TrustedEtransFlags')->nullable();

            $table->integer('CobInsPaidBehaviorOverride')->nullable();

            $table->integer('EraAutomationOverride')->nullable();

            $table->integer('OrthoInsPayConsolidate')->nullable();

            $table->integer('PaySuiteTransSup')->nullable();

            $table->text('PreAuthCodes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_carriers');
    }
};
