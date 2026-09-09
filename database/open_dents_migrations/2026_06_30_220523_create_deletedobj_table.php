<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('deletedobj', function (Blueprint $table) {

            $table->integer('DeletedObjNum');

            $table->date('DateTEntry');

            $table->integer('ObjType');

            $table->text('ObjSerialized');

        });

    }

    public function down()
    {
        Schema::dropIfExists('deletedobj');
    }
};
