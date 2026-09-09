<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('procbutton', function (Blueprint $table) {

            $table->integer('ProcButtonNum');

            $table->string('Description');

            $table->integer('ItemOrder');

            $table->integer('Category');

            $table->text('ButtonImage');

            $table->integer('IsMultiVisit');

        });

    }

    public function down()
    {
        Schema::dropIfExists('procbutton');
    }
};
