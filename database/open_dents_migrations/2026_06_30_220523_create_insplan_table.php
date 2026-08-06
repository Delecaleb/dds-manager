<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {

    public function up()
    {

        Schema::create('insplan', function (Blueprint $table) {

            $table->integer('PlanNum');

            $table->string('GroupName');

            $table->string('GroupNum');

            $table->text('PlanNote');

            $table->integer('FeeSched');

            $table->string('PlanType');

            $table->integer('ClaimFormNum');

            $table->integer('UseAltCode');

            $table->integer('ClaimsUseUCR');

            $table->integer('CopayFeeSched');

            $table->integer('EmployerNum');

            $table->integer('CarrierNum');

            $table->integer('AllowedFeeSched');

            $table->string('TrojanID');

            $table->string('DivisionNo');

            $table->integer('IsMedical');

            $table->integer('FilingCode');

            $table->integer('DentaideCardSequence');

            $table->integer('ShowBaseUnits');

            $table->integer('CodeSubstNone');

            $table->integer('IsHidden');

            $table->integer('MonthRenew');

            $table->integer('FilingCodeSubtype');

            $table->string('CanadianPlanFlag');

            $table->string('CanadianDiagnosticCode');

            $table->string('CanadianInstitutionCode');

            $table->string('RxBIN');

            $table->integer('CobRule');

            $table->string('SopCode');

            $table->integer('SecUserNumEntry');

            $table->date('SecDateEntry');

            $table->string('SecDateTEdit');

            $table->integer('HideFromVerifyList');

            $table->integer('OrthoType');

            $table->integer('OrthoAutoProcFreq');

            $table->integer('OrthoAutoProcCodeNumOverride');

            $table->string('OrthoAutoFeeBilled');

            $table->integer('OrthoAutoClaimDaysWait');

            $table->integer('BillingType');

            $table->integer('HasPpoSubstWriteoffs');

            $table->integer('ExclusionFeeRule');

            $table->integer('ManualFeeSchedNum');

            $table->integer('IsBlueBookEnabled');

            $table->integer('InsPlansZeroWriteOffsOnAnnualMaxOverride');

            $table->integer('InsPlansZeroWriteOffsOnFreqOrAgingOverride');

            $table->string('PerVisitPatAmount');

            $table->string('PerVisitInsAmount');



        });

    }


    public function down()
    {
        Schema::dropIfExists('insplan');
    }

};
