<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('ehrlabspecimencondition', function (Blueprint $table) {

            $table->integer('EhrLabSpecimenConditionNum');

            $table->integer('EhrLabSpecimenNum');

            $table->string('SpecimenConditionID');

            $table->string('SpecimenConditionText');

            $table->string('SpecimenConditionCodeSystemName');

            $table->string('SpecimenConditionIDAlt');

            $table->string('SpecimenConditionTextAlt');

            $table->string('SpecimenConditionCodeSystemNameAlt');

            $table->string('SpecimenConditionTextOriginal');

        });

    }

    public function down()
    {
        Schema::dropIfExists('ehrlabspecimencondition');
    }
};
