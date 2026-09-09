<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('medlab', function (Blueprint $table) {

            $table->integer('MedLabNum');

            $table->string('SendingApp');

            $table->string('SendingFacility');

            $table->integer('PatNum');

            $table->integer('ProvNum');

            $table->string('PatIDLab');

            $table->string('PatIDAlt');

            $table->string('PatAge');

            $table->string('PatAccountNum');

            $table->integer('PatFasting');

            $table->string('SpecimenID');

            $table->string('SpecimenIDFiller');

            $table->string('ObsTestID');

            $table->string('ObsTestDescript');

            $table->string('ObsTestLoinc');

            $table->string('ObsTestLoincText');

            $table->date('DateTimeCollected');

            $table->string('TotalVolume');

            $table->string('ActionCode');

            $table->string('ClinicalInfo');

            $table->date('DateTimeEntered');

            $table->string('OrderingProvNPI');

            $table->string('OrderingProvLocalID');

            $table->string('OrderingProvLName');

            $table->string('OrderingProvFName');

            $table->string('SpecimenIDAlt');

            $table->date('DateTimeReported');

            $table->string('ResultStatus');

            $table->string('ParentObsID');

            $table->string('ParentObsTestID');

            $table->text('NotePat');

            $table->text('NoteLab');

            $table->string('FileName');

            $table->text('OriginalPIDSegment');

        });

    }

    public function down()
    {
        Schema::dropIfExists('medlab');
    }
};
