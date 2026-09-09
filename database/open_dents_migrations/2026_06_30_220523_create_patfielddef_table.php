<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('patfielddef', function (Blueprint $table) {

            $table->integer('PatFieldDefNum');

            $table->string('FieldName');

            $table->integer('FieldType');

            $table->text('PickList');

            $table->integer('ItemOrder');

            $table->integer('IsHidden');

        });

    }

    public function down()
    {
        Schema::dropIfExists('patfielddef');
    }
};
