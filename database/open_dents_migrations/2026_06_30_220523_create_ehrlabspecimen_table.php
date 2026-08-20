<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('ehrlabspecimen', function (Blueprint $table) {

            $table->integer('EhrLabSpecimenNum');

            $table->integer('EhrLabNum');

            $table->integer('SetIdSPM');

            $table->string('SpecimenTypeID');

            $table->string('SpecimenTypeText');

            $table->string('SpecimenTypeCodeSystemName');

            $table->string('SpecimenTypeIDAlt');

            $table->string('SpecimenTypeTextAlt');

            $table->string('SpecimenTypeCodeSystemNameAlt');

            $table->string('SpecimenTypeTextOriginal');

            $table->string('CollectionDateTimeStart');

            $table->string('CollectionDateTimeEnd');

        });

    }

    public function down()
    {
        Schema::dropIfExists('ehrlabspecimen');
    }
};
