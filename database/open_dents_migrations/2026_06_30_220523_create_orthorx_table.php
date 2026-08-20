<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('orthorx', function (Blueprint $table) {

            $table->integer('OrthoRxNum');

            $table->integer('OrthoHardwareSpecNum');

            $table->string('Description');

            $table->string('ToothRange');

            $table->integer('ItemOrder');

        });

    }

    public function down()
    {
        Schema::dropIfExists('orthorx');
    }
};
