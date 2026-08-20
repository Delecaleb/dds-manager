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
        Schema::create('od_providers', function (Blueprint $table) {
            $table->id();
            $table->string('ProvNum')->unique();
            $table->string('Abbr')->nullable();
            $table->string('ItemOrder')->nullable();
            $table->string('LName')->nullable();
            $table->string('PName')->nullable();
            $table->string('MI')->nullable();
            $table->string('Suffix')->nullable();
            $table->string('FeeSched')->nullable();
            $table->string('Specialty')->nullable();
            $table->string('SSN')->nullable();
            $table->string('StateLicense')->nullable();
            $table->string('DEANum')->nullable();
            $table->string('IsSecondary')->nullable();
            $table->string('ProvColor')->nullable();
            $table->string('IsHidden')->nullable();
            $table->string('UsingTIN')->nullable();
            $table->string('BlueCrossID')->nullable();
            $table->string('SigOnFile')->nullable();
            $table->string('MedicaidID')->nullable();
            $table->string('OutlineColor')->nullable();
            $table->string('SchoolClassNum')->nullable();
            $table->string('NationalProvID')->nullable();
            $table->string('CanadianOfficeNum')->nullable();
            $table->string('DateTStamp')->nullable();
            $table->string('AnesthProvType')->nullable();
            $table->string('TaxonomyCodeOverride')->nullable();
            $table->string('IsCDAnet')->nullable();
            $table->string('EcwID')->nullable();
            $table->string('StateRxID')->nullable();
            $table->string('IsNotPerson')->nullable();
            $table->string('StateWhereLicensed')->nullable();
            $table->string('EmailAddressNum')->nullable();
            $table->string('IsInstructor')->nullable();
            $table->string('EhrMuStage')->nullable();
            $table->string('ProvNumBillingOverride')->nullable();
            $table->string('CustomID')->nullable();
            $table->string('ProvStatus')->nullable();
            $table->string('IsHiddenReport')->nullable();
            $table->string('IsErxEnabled')->nullable();
            $table->string('Birthdate')->nullable();
            $table->string('SchedNote')->nullable();
            $table->string('WebSchedDescript')->nullable();
            $table->string('WebSchedFaceT')->nullable();
            $table->string('WebSchedImageLocation')->nullable();
            $table->string('HourlyProdGoalAmt')->nullable();
            $table->string('DateTerm')->nullable();
            $table->string('PreferredName')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_providers');
    }
};
