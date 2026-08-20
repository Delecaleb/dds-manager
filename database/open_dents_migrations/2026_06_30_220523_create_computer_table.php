<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('computer', function (Blueprint $table) {

            $table->integer('ComputerNum');

            $table->string('CompName');

            $table->date('LastHeartBeat');

        });

    }

    public function down()
    {
        Schema::dropIfExists('computer');
    }
};
