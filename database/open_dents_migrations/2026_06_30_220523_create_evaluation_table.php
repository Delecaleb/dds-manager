<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('evaluation', function (Blueprint $table) {

            $table->integer('EvaluationNum');

            $table->integer('InstructNum');

            $table->integer('StudentNum');

            $table->integer('SchoolCourseNum');

            $table->string('EvalTitle');

            $table->date('DateEval');

            $table->integer('GradingScaleNum');

            $table->string('OverallGradeShowing');

            $table->string('OverallGradeNumber');

            $table->text('Notes');

            $table->string('GradeOverride');

        });

    }

    public function down()
    {
        Schema::dropIfExists('evaluation');
    }
};
