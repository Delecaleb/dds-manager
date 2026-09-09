<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('schoolclass', function (Blueprint $table) {

            $table->integer('SchoolClassNum');

            $table->integer('GradYear');

            $table->string('Descript');

        });

    }

    public function down()
    {
        Schema::dropIfExists('schoolclass');
    }
};
