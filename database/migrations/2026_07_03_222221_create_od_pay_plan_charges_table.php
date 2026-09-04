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
        Schema::create('od_pay_plan_charges', function (Blueprint $table) {
            $table->id();
            $table->integer('PayPlanChargeNum')->nullable();
            $table->integer('PayPlanNum')->nullable();
            $table->integer('Guarantor')->nullable();
            $table->integer('PatNum')->nullable();
            $table->date('ChargeDate')->nullable();
            $table->string('Principal')->nullable();
            $table->string('Interest')->nullable();
            $table->text('Note')->nullable();
            $table->integer('ProvNum')->nullable();
            $table->integer('ClinicNum')->nullable();
            $table->integer('ChargeType')->nullable();
            $table->integer('ProcNum')->nullable();
            $table->date('SecDateTEntry')->nullable();
            $table->string('SecDateTEdit')->nullable();
            $table->integer('StatementNum')->nullable();
            $table->integer('FKey')->nullable();
            $table->integer('LinkType')->nullable();
            $table->integer('IsOffset')->nullable();
            $table->integer('IsDownPayment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_pay_plan_charges');
    }
};
