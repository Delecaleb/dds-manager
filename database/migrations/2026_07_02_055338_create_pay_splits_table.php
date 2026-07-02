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
        Schema::create('od_pay_splits', function (Blueprint $table) {
            $table->id();
            $table->integer('SplitNum')->nullable();

            $table->string('SplitAmt')->nullable();

            $table->integer('PatNum')->nullable();

            $table->date('ProcDate')->nullable();

            $table->integer('PayNum')->nullable();

            $table->integer('IsDiscount')->nullable();

            $table->integer('DiscountType')->nullable();

            $table->integer('ProvNum')->nullable();

            $table->integer('PayPlanNum')->nullable();

            $table->date('DatePay')->nullable();

            $table->integer('ProcNum')->nullable();

            $table->date('DateEntry')->nullable();

            $table->integer('UnearnedType')->nullable();

            $table->integer('ClinicNum')->nullable();

            $table->integer('SecUserNumEntry')->nullable();

            $table->string('SecDateTEdit')->nullable();

            $table->integer('FSplitNum')->nullable();

            $table->integer('AdjNum')->nullable();

            $table->integer('PayPlanChargeNum')->nullable();

            $table->integer('PayPlanDebitType')->nullable();

            $table->string('SecurityHash')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_splits');
    }
};
