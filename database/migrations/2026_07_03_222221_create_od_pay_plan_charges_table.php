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
            $table->integer('PayPlanChargeNum');

            $table->integer('PayPlanNum');

            $table->integer('Guarantor');

            $table->integer('PatNum');

            $table->date('ChargeDate');

            $table->string('Principal');

            $table->string('Interest');

            $table->text('Note');

            $table->integer('ProvNum');

            $table->integer('ClinicNum');

            $table->integer('ChargeType');

            $table->integer('ProcNum');

            $table->date('SecDateTEntry');

            $table->string('SecDateTEdit');

            $table->integer('StatementNum');

            $table->integer('FKey');

            $table->integer('LinkType');

            $table->integer('IsOffset');

            $table->integer('IsDownPayment');
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
