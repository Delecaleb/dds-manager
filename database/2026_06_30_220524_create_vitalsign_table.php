<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('vitalsign', function (Blueprint $table) {

            $table->integer('VitalsignNum');

            $table->integer('PatNum');

            $table->string('Height');

            $table->string('Weight');

            $table->integer('BpSystolic');

            $table->integer('BpDiastolic');

            $table->date('DateTaken');

            $table->integer('HasFollowupPlan');

            $table->integer('IsIneligible');

            $table->text('Documentation');

            $table->integer('ChildGotNutrition');

            $table->integer('ChildGotPhysCouns');

            $table->string('WeightCode');

            $table->string('HeightExamCode');

            $table->string('WeightExamCode');

            $table->string('BMIExamCode');

            $table->integer('EhrNotPerformedNum');

            $table->integer('PregDiseaseNum');

            $table->integer('BMIPercentile');

            $table->integer('Pulse');

        });

    }

    public function down()
    {
        Schema::dropIfExists('vitalsign');
    }
};
