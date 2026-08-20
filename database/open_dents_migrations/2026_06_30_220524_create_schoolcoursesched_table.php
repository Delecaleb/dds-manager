<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('schoolcoursesched', function (Blueprint $table) {

            $table->integer('SchoolCourseSchedNum');

            $table->integer('SchoolCourseDefNum');

            $table->integer('SchoolCourseNum');

            $table->string('TimeStart');

            $table->string('TimeEnd');

            $table->integer('DayOfTheWeek');

            $table->date('DateOverride');

            $table->integer('IsOverride');

            $table->integer('IsCanceled');

        });

    }

    public function down()
    {
        Schema::dropIfExists('schoolcoursesched');
    }
};
