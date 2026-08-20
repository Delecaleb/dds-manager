<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('eduresource', function (Blueprint $table) {

            $table->integer('EduResourceNum');

            $table->integer('DiseaseDefNum');

            $table->integer('MedicationNum');

            $table->string('LabResultID');

            $table->string('LabResultName');

            $table->string('LabResultCompare');

            $table->string('ResourceUrl');

            $table->string('SmokingSnoMed');

        });

    }

    public function down()
    {
        Schema::dropIfExists('eduresource');
    }
};
