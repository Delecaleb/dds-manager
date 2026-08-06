<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('od_insplans', function (Blueprint $table) {
            $table->id();
            $table->integer('PlanNum')->nullable();

            $table->string('GroupName')->nullable();

            $table->string('GroupNum')->nullable();

            $table->text('PlanNote')->nullable();

            $table->integer('FeeSched')->nullable();

            $table->string('PlanType')->nullable();

            $table->integer('ClaimFormNum')->nullable();

            $table->integer('UseAltCode')->nullable();

            $table->integer('ClaimsUseUCR')->nullable();

            $table->integer('CopayFeeSched')->nullable();

            $table->integer('EmployerNum')->nullable();

            $table->integer('CarrierNum')->nullable();

            $table->integer('AllowedFeeSched')->nullable();

            $table->string('TrojanID')->nullable();

            $table->string('DivisionNo')->nullable();

            $table->integer('IsMedical')->nullable();

            $table->integer('FilingCode')->nullable();

            $table->integer('DentaideCardSequence')->nullable();

            $table->integer('ShowBaseUnits')->nullable();

            $table->integer('CodeSubstNone')->nullable();

            $table->integer('IsHidden')->nullable();

            $table->integer('MonthRenew')->nullable();

            $table->integer('FilingCodeSubtype')->nullable();

            $table->string('CanadianPlanFlag')->nullable();

            $table->string('CanadianDiagnosticCode')->nullable();

            $table->string('CanadianInstitutionCode')->nullable();

            $table->string('RxBIN')->nullable();

            $table->integer('CobRule')->nullable();

            $table->string('SopCode')->nullable();

            $table->integer('SecUserNumEntry')->nullable();

            $table->date('SecDateEntry')->nullable();

            $table->string('SecDateTEdit')->nullable();

            $table->integer('HideFromVerifyList')->nullable();

            $table->integer('OrthoType')->nullable();

            $table->integer('OrthoAutoProcFreq')->nullable();

            $table->integer('OrthoAutoProcCodeNumOverride')->nullable();

            $table->string('OrthoAutoFeeBilled')->nullable();

            $table->integer('OrthoAutoClaimDaysWait')->nullable();

            $table->integer('BillingType')->nullable();

            $table->integer('HasPpoSubstWriteoffs')->nullable();

            $table->integer('ExclusionFeeRule')->nullable();

            $table->integer('ManualFeeSchedNum')->nullable();

            $table->integer('IsBlueBookEnabled')->nullable();

            $table->integer('InsPlansZeroWriteOffsOnAnnualMaxOverride')->nullable();

            $table->integer('InsPlansZeroWriteOffsOnFreqOrAgingOverride')->nullable();

            $table->string('PerVisitPatAmount')->nullable();

            $table->string('PerVisitInsAmount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_insplans');
    }
};
