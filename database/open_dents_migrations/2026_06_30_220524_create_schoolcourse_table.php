<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('schoolcourse', function (Blueprint $table) {

            $table->integer('SchoolCourseNum');

            $table->string('CourseID');

            $table->string('Descript');

            $table->date('DateStart');

            $table->date('DateEnd');

            $table->integer('SchoolClassNum');

            $table->integer('GradingScaleNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('schoolcourse');
    }
};
