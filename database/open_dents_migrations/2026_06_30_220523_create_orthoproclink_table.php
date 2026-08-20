<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('orthoproclink', function (Blueprint $table) {

            $table->integer('OrthoProcLinkNum');

            $table->integer('OrthoCaseNum');

            $table->integer('ProcNum');

            $table->date('SecDateTEntry');

            $table->integer('SecUserNumEntry');

            $table->integer('ProcLinkType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('orthoproclink');
    }
};
