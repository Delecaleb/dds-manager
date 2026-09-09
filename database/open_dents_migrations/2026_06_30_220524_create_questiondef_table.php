<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('questiondef', function (Blueprint $table) {

            $table->integer('QuestionDefNum');

            $table->text('Description');

            $table->integer('ItemOrder');

            $table->integer('QuestType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('questiondef');
    }
};
