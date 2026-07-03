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
        Schema::create('od_claim_procs', function (Blueprint $table) {
            $table->id();
            $table->integer('ClaimProcNum');

            $table->integer('ProcNum')->nullable();

            $table->integer('ClaimNum')->nullable();

            $table->integer('PatNum')->nullable();

            $table->integer('ProvNum')->nullable();

            $table->string('FeeBilled')->nullable();

            $table->string('InsPayEst')->nullable();

            $table->string('DedApplied')->nullable();

            $table->integer('Status');

            $table->string('InsPayAmt')->nullable();

            $table->string('Remarks')->nullable();

            $table->integer('ClaimPaymentNum');

            $table->integer('PlanNum')->nullable();

            $table->date('DateCP')->nullable();

            $table->string('WriteOff')->nullable();

            $table->string('CodeSent')->nullable();

            $table->string('AllowedOverride')->nullable();

            $table->integer('Percentage')->nullable();

            $table->integer('PercentOverride')->nullable();

            $table->string('CopayAmt')->nullable();

            $table->integer('NoBillIns')->nullable();

            $table->string('PaidOtherIns')->nullable();

            $table->string('BaseEst')->nullable();

            $table->string('CopayOverride')->nullable();

            $table->date('ProcDate')->nullable();

            $table->date('DateEntry')->nullable();

            $table->integer('LineNumber')->nullable();

            $table->string('DedEst')->nullable();

            $table->string('DedEstOverride')->nullable();

            $table->string('InsEstTotal')->nullable();

            $table->string('InsEstTotalOverride')->nullable();

            $table->string('PaidOtherInsOverride')->nullable();

            $table->string('EstimateNote')->nullable();

            $table->string('WriteOffEst')->nullable();

            $table->string('WriteOffEstOverride')->nullable();

            $table->integer('ClinicNum')->nullable();

            $table->integer('InsSubNum')->nullable();

            $table->integer('PaymentRow')->nullable();

            $table->integer('PayPlanNum')->nullable();

            $table->integer('ClaimPaymentTracking')->nullable();

            $table->integer('SecUserNumEntry')->nullable();

            $table->date('SecDateEntry')->nullable();

            $table->string('SecDateTEdit')->nullable();

            $table->date('DateSuppReceived')->nullable();

            $table->date('DateInsFinalized')->nullable();

            $table->integer('IsTransfer')->nullable();

            $table->string('ClaimAdjReasonCodes')->nullable();

            $table->integer('IsOverpay')->nullable();

            $table->string('SecurityHash')->nullable();

            $table->integer('Etrans835AttachNum')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_claim_procs');
    }
};
