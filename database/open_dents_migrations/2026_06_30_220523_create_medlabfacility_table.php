<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('medlabfacility', function (Blueprint $table) {

            $table->integer('MedLabFacilityNum');

            $table->string('FacilityName');

            $table->string('Address');

            $table->string('City');

            $table->string('State');

            $table->string('Zip');

            $table->string('Phone');

            $table->string('DirectorTitle');

            $table->string('DirectorLName');

            $table->string('DirectorFName');

        });

    }

    public function down()
    {
        Schema::dropIfExists('medlabfacility');
    }
};
