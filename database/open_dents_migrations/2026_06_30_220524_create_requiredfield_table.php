<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('requiredfield', function (Blueprint $table) {

            $table->integer('RequiredFieldNum');

            $table->integer('FieldType');

            $table->string('FieldName');

        });

    }

    public function down()
    {
        Schema::dropIfExists('requiredfield');
    }
};
