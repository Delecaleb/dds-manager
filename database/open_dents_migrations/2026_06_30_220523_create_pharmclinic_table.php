<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('pharmclinic', function (Blueprint $table) {

            $table->integer('PharmClinicNum');

            $table->integer('PharmacyNum');

            $table->integer('ClinicNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('pharmclinic');
    }
};
