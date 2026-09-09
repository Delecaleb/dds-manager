<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('payplanlink', function (Blueprint $table) {

            $table->integer('PayPlanLinkNum');

            $table->integer('PayPlanNum');

            $table->integer('LinkType');

            $table->integer('FKey');

            $table->string('AmountOverride');

            $table->date('SecDateTEntry');

        });

    }

    public function down()
    {
        Schema::dropIfExists('payplanlink');
    }
};
