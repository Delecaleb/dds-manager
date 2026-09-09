<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('eroutingdeflink', function (Blueprint $table) {

            $table->integer('ERoutingDefLinkNum');

            $table->integer('ERoutingDefNum');

            $table->integer('Fkey');

            $table->integer('ERoutingType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('eroutingdeflink');
    }
};
