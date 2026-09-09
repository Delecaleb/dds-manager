<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('installmentplan', function (Blueprint $table) {

            $table->integer('InstallmentPlanNum');

            $table->integer('PatNum');

            $table->date('DateAgreement');

            $table->date('DateFirstPayment');

            $table->string('MonthlyPayment');

            $table->string('APR');

            $table->string('Note');

        });

    }

    public function down()
    {
        Schema::dropIfExists('installmentplan');
    }
};
