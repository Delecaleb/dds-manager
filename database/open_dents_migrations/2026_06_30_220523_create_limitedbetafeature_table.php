<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('limitedbetafeature', function (Blueprint $table) {

            $table->integer('LimitedBetaFeatureNum');

            $table->integer('LimitedBetaFeatureTypeNum');

            $table->integer('ClinicNum');

            $table->integer('IsSignedUp');

        });

    }

    public function down()
    {
        Schema::dropIfExists('limitedbetafeature');
    }
};
