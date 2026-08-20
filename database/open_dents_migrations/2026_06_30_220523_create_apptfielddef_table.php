<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('apptfielddef', function (Blueprint $table) {

            $table->integer('ApptFieldDefNum');

            $table->string('FieldName');

            $table->integer('FieldType');

            $table->text('PickList');

            $table->integer('ItemOrder');

        });

    }

    public function down()
    {
        Schema::dropIfExists('apptfielddef');
    }
};
