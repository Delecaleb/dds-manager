<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('ehrtrigger', function (Blueprint $table) {

            $table->integer('EhrTriggerNum');

            $table->string('Description');

            $table->text('ProblemSnomedList');

            $table->text('ProblemIcd9List');

            $table->text('ProblemIcd10List');

            $table->text('ProblemDefNumList');

            $table->text('MedicationNumList');

            $table->text('RxCuiList');

            $table->text('CvxList');

            $table->text('AllergyDefNumList');

            $table->text('DemographicsList');

            $table->text('LabLoincList');

            $table->text('VitalLoincList');

            $table->text('Instructions');

            $table->text('Bibliography');

            $table->integer('Cardinality');

        });

    }

    public function down()
    {
        Schema::dropIfExists('ehrtrigger');
    }
};
