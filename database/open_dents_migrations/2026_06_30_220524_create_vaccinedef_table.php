<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('vaccinedef', function (Blueprint $table) {

            $table->integer('VaccineDefNum');

            $table->string('CVXCode');

            $table->string('VaccineName');

            $table->integer('DrugManufacturerNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('vaccinedef');
    }
};
