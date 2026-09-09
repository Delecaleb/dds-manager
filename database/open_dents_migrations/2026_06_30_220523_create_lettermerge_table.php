<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('lettermerge', function (Blueprint $table) {

            $table->integer('LetterMergeNum');

            $table->string('Description');

            $table->string('TemplateName');

            $table->string('DataFileName');

            $table->integer('Category');

            $table->integer('ImageFolder');

        });

    }

    public function down()
    {
        Schema::dropIfExists('lettermerge');
    }
};
