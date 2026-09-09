<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('sequencecounter', function (Blueprint $table) {

            $table->integer('CounterNum');

            $table->string('CounterName');

            $table->integer('CounterVal');

        });

    }

    public function down()
    {
        Schema::dropIfExists('sequencecounter');
    }
};
