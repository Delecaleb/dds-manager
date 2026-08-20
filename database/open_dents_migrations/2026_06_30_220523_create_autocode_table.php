<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('autocode', function (Blueprint $table) {

            $table->integer('AutoCodeNum');

            $table->string('Description');

            $table->integer('IsHidden');

            $table->integer('LessIntrusive');

        });

    }

    public function down()
    {
        Schema::dropIfExists('autocode');
    }
};
