<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('question', function (Blueprint $table) {

            $table->integer('QuestionNum');

            $table->integer('PatNum');

            $table->integer('ItemOrder');

            $table->text('Description');

            $table->text('Answer');

            $table->integer('FormPatNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('question');
    }
};
