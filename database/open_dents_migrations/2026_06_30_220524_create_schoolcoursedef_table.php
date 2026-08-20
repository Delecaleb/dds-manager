<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('schoolcoursedef', function (Blueprint $table) {

            $table->integer('SchoolCourseDefNum');

            $table->string('CourseID');

            $table->string('Descript');

            $table->integer('GradingScaleNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('schoolcoursedef');
    }
};
