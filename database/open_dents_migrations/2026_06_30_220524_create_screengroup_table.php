<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('screengroup', function (Blueprint $table) {

            $table->integer('ScreenGroupNum');

            $table->string('Description');

            $table->date('SGDate');

            $table->string('ProvName');

            $table->integer('ProvNum');

            $table->integer('PlaceService');

            $table->string('County');

            $table->string('GradeSchool');

            $table->integer('SheetDefNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('screengroup');
    }
};
