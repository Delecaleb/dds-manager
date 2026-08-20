<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('treatplanparam', function (Blueprint $table) {

            $table->integer('TreatPlanParamNum');

            $table->integer('PatNum');

            $table->integer('TreatPlanNum');

            $table->integer('ShowDiscount');

            $table->integer('ShowMaxDed');

            $table->integer('ShowSubTotals');

            $table->integer('ShowTotals');

            $table->integer('ShowCompleted');

            $table->integer('ShowFees');

            $table->integer('ShowIns');

        });

    }

    public function down()
    {
        Schema::dropIfExists('treatplanparam');
    }
};
