<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('loinc', function (Blueprint $table) {

            $table->integer('LoincNum');

            $table->string('LoincCode');

            $table->string('Component');

            $table->string('PropertyObserved');

            $table->string('TimeAspct');

            $table->string('SystemMeasured');

            $table->string('ScaleType');

            $table->string('MethodType');

            $table->string('StatusOfCode');

            $table->string('NameShort');

            $table->string('ClassType');

            $table->integer('UnitsRequired');

            $table->string('OrderObs');

            $table->string('HL7FieldSubfieldID');

            $table->text('ExternalCopyrightNotice');

            $table->string('NameLongCommon');

            $table->string('UnitsUCUM');

            $table->integer('RankCommonTests');

            $table->integer('RankCommonOrders');

        });

    }

    public function down()
    {
        Schema::dropIfExists('loinc');
    }
};
