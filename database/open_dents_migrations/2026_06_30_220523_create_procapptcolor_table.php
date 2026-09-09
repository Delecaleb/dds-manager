<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('procapptcolor', function (Blueprint $table) {

            $table->integer('ProcApptColorNum');

            $table->string('CodeRange');

            $table->integer('ColorText');

            $table->integer('ShowPreviousDate');

        });

    }

    public function down()
    {
        Schema::dropIfExists('procapptcolor');
    }
};
