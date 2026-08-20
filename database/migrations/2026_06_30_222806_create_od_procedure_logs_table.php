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
        Schema::create('od_procedure_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ProcNum')->nullable();

            $table->string('PatNum')->nullable();

            $table->string('AptNum')->nullable();

            $table->string('OldCode')->nullable();

            $table->date('ProcDate')->nullable();

            $table->string('ProcFee')->nullable();

            $table->string('Surf')->nullable();

            $table->string('ToothNum')->nullable();

            $table->string('ToothRange')->nullable();

            $table->string('Priority')->nullable();

            $table->string('ProcStatus')->nullable();

            $table->string('ProvNum')->nullable();

            $table->string('Dx')->nullable();

            $table->string('PlannedAptNum')->nullable();

            $table->string('PlaceService')->nullable();

            $table->string('Prosthesis')->nullable();

            $table->date('DateOriginalProsth')->nullable();

            $table->string('ClaimNote')->nullable();

            $table->date('DateEntryC')->nullable();

            $table->string('ClinicNum')->nullable();

            $table->string('MedicalCode')->nullable();

            $table->string('DiagnosticCode')->nullable();

            $table->string('IsPrincDiag')->nullable();

            $table->string('ProcNumLab')->nullable();

            $table->string('BillingTypeOne')->nullable();

            $table->string('BillingTypeTwo')->nullable();

            $table->string('CodeNum')->nullable();

            $table->string('CodeMod1')->nullable();

            $table->string('CodeMod2')->nullable();

            $table->string('CodeMod3')->nullable();

            $table->string('CodeMod4')->nullable();

            $table->string('RevCode')->nullable();

            $table->string('UnitQty')->nullable();

            $table->string('BaseUnits')->nullable();

            $table->string('StartTime')->nullable();

            $table->string('StopTime')->nullable();

            $table->date('DateTP')->nullable();

            $table->string('SiteNum')->nullable();

            $table->string('HideGraphics')->nullable();

            $table->string('CanadianTypeCodes')->nullable();

            $table->string('ProcTime')->nullable();

            $table->string('ProcTimeEnd')->nullable();

            $table->string('DateTStamp')->nullable();

            $table->string('Prognosis')->nullable();

            $table->string('DrugUnit')->nullable();

            $table->string('DrugQty')->nullable();

            $table->string('UnitQtyType')->nullable();

            $table->string('StatementNum')->nullable();

            $table->string('IsLocked')->nullable();

            $table->string('BillingNote')->nullable();

            $table->string('RepeatChargeNum')->nullable();

            $table->string('SnomedBodySite')->nullable();

            $table->string('DiagnosticCode2')->nullable();

            $table->string('DiagnosticCode3')->nullable();

            $table->string('DiagnosticCode4')->nullable();

            $table->string('ProvOrderOverride')->nullable();

            $table->string('Discount')->nullable();

            $table->string('IsDateProsthEst')->nullable();

            $table->string('IcdVersion')->nullable();

            $table->string('IsCpoe')->nullable();

            $table->string('SecUserNumEntry')->nullable();

            $table->date('SecDateEntry')->nullable();

            $table->date('DateComplete')->nullable();

            $table->string('OrderingReferralNum')->nullable();

            $table->string('TaxAmt')->nullable();

            $table->string('Urgency')->nullable();

            $table->string('DiscountPlanAmt')->nullable();

            $table->string('NoBillIns')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_procedure_logs');
    }
};
