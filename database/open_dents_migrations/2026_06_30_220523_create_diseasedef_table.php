<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('diseasedef', function (Blueprint $table) {

            $table->integer('DiseaseDefNum');

            $table->string('DiseaseName');

            $table->integer('ItemOrder');

            $table->integer('IsHidden');

            $table->string('DateTStamp');

            $table->string('ICD9Code');

            $table->string('SnomedCode');

            $table->string('Icd10Code');

        });

    }

    public function down()
    {
        Schema::dropIfExists('diseasedef');
    }
};
