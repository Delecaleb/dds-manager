<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('evaluationcriterion', function (Blueprint $table) {

            $table->integer('EvaluationCriterionNum');

            $table->integer('EvaluationNum');

            $table->string('CriterionDescript');

            $table->integer('IsCategoryName');

            $table->integer('GradingScaleNum');

            $table->string('GradeShowing');

            $table->string('GradeNumber');

            $table->text('Notes');

            $table->integer('ItemOrder');

            $table->string('MaxPointsPoss');

        });

    }

    public function down()
    {
        Schema::dropIfExists('evaluationcriterion');
    }
};
