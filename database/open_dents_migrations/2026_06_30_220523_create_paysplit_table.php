<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('paysplit', function (Blueprint $table) {

            $table->integer('SplitNum');

            $table->string('SplitAmt');

            $table->integer('PatNum');

            $table->date('ProcDate');

            $table->integer('PayNum');

            $table->integer('IsDiscount');

            $table->integer('DiscountType');

            $table->integer('ProvNum');

            $table->integer('PayPlanNum');

            $table->date('DatePay');

            $table->integer('ProcNum');

            $table->date('DateEntry');

            $table->integer('UnearnedType');

            $table->integer('ClinicNum');

            $table->integer('SecUserNumEntry');

            $table->string('SecDateTEdit');

            $table->integer('FSplitNum');

            $table->integer('AdjNum');

            $table->integer('PayPlanChargeNum');

            $table->integer('PayPlanDebitType');

            $table->string('SecurityHash');

        });

    }

    public function down()
    {
        Schema::dropIfExists('paysplit');
    }
};
