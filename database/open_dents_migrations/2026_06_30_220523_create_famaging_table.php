<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('famaging', function (Blueprint $table) {

            $table->integer('PatNum');

            $table->string('Bal_0_30');

            $table->string('Bal_31_60');

            $table->string('Bal_61_90');

            $table->string('BalOver90');

            $table->string('InsEst');

            $table->string('BalTotal');

            $table->string('PayPlanDue');

        });

    }

    public function down()
    {
        Schema::dropIfExists('famaging');
    }
};
