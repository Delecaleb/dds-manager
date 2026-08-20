<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('gradingscale', function (Blueprint $table) {

            $table->integer('GradingScaleNum');

            $table->string('Description');

            $table->integer('ScaleType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('gradingscale');
    }
};
