<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('cdspermission', function (Blueprint $table) {

            $table->integer('CDSPermissionNum');

            $table->integer('UserNum');

            $table->integer('SetupCDS');

            $table->integer('ShowCDS');

            $table->integer('ShowInfobutton');

            $table->integer('EditBibliography');

            $table->integer('ProblemCDS');

            $table->integer('MedicationCDS');

            $table->integer('AllergyCDS');

            $table->integer('DemographicCDS');

            $table->integer('LabTestCDS');

            $table->integer('VitalCDS');

        });

    }

    public function down()
    {
        Schema::dropIfExists('cdspermission');
    }
};
