<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('wikilisthist', function (Blueprint $table) {

            $table->integer('WikiListHistNum');

            $table->integer('UserNum');

            $table->string('ListName');

            $table->text('ListHeaders');

            $table->text('ListContent');

            $table->date('DateTimeSaved');

        });

    }

    public function down()
    {
        Schema::dropIfExists('wikilisthist');
    }
};
