<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('covcat', function (Blueprint $table) {

            $table->integer('CovCatNum');

            $table->string('Description');

            $table->integer('DefaultPercent');

            $table->integer('CovOrder');

            $table->integer('IsHidden');

            $table->integer('EbenefitCat');

        });

    }

    public function down()
    {
        Schema::dropIfExists('covcat');
    }
};
