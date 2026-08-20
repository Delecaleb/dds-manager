<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('evaluationdef', function (Blueprint $table) {

            $table->integer('EvaluationDefNum');

            $table->integer('SchoolCourseNum');

            $table->string('EvalTitle');

            $table->integer('GradingScaleNum');

            $table->integer('SchoolCourseDefNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('evaluationdef');
    }
};
