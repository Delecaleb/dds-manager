<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('reqneeded', function (Blueprint $table) {

            $table->integer('ReqNeededNum');

            $table->string('Descript');

            $table->integer('SchoolCourseNum');

            $table->integer('SchoolClassNum');

            $table->integer('SchoolCourseDefNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('reqneeded');
    }
};
