<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('orthoplanlink', function (Blueprint $table) {

            $table->integer('OrthoPlanLinkNum');

            $table->integer('OrthoCaseNum');

            $table->integer('LinkType');

            $table->integer('FKey');

            $table->integer('IsActive');

            $table->date('SecDateTEntry');

            $table->integer('SecUserNumEntry');

        });

    }

    public function down()
    {
        Schema::dropIfExists('orthoplanlink');
    }
};
