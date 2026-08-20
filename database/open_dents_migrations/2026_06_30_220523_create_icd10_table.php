<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('icd10', function (Blueprint $table) {

            $table->integer('Icd10Num');

            $table->string('Icd10Code');

            $table->string('Description');

            $table->string('IsCode');

        });

    }

    public function down()
    {
        Schema::dropIfExists('icd10');
    }
};
