<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('recurringcharge', function (Blueprint $table) {

            $table->integer('RecurringChargeNum');

            $table->integer('PatNum');

            $table->integer('ClinicNum');

            $table->date('DateTimeCharge');

            $table->integer('ChargeStatus');

            $table->string('FamBal');

            $table->string('PayPlanDue');

            $table->string('TotalDue');

            $table->string('RepeatAmt');

            $table->string('ChargeAmt');

            $table->integer('UserNum');

            $table->integer('PayNum');

            $table->integer('CreditCardNum');

            $table->text('ErrorMsg');

        });

    }

    public function down()
    {
        Schema::dropIfExists('recurringcharge');
    }
};
