<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('covspan', function (Blueprint $table) {

            $table->integer('CovSpanNum');

            $table->integer('CovCatNum');

            $table->string('FromCode');

            $table->string('ToCode');

        });

    }

    public function down()
    {
        Schema::dropIfExists('covspan');
    }
};
