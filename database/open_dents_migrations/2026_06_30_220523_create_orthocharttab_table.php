<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('orthocharttab', function (Blueprint $table) {

            $table->integer('OrthoChartTabNum');

            $table->string('TabName');

            $table->integer('ItemOrder');

            $table->integer('IsHidden');

        });

    }

    public function down()
    {
        Schema::dropIfExists('orthocharttab');
    }
};
