<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('lettermergefield', function (Blueprint $table) {

            $table->integer('FieldNum');

            $table->integer('LetterMergeNum');

            $table->string('FieldName');

        });

    }

    public function down()
    {
        Schema::dropIfExists('lettermergefield');
    }
};
