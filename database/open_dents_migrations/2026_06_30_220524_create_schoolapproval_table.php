<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('schoolapproval', function (Blueprint $table) {

            $table->integer('SchoolApprovalNum');

            $table->integer('ProvNum');

            $table->integer('SignOffStatus');

            $table->integer('InstructorNum');

            $table->integer('AptNum');

            $table->integer('ProcNum');

            $table->integer('TreatPlanNum');

            $table->integer('PerioExamNum');

            $table->integer('AllergyNum');

            $table->integer('DiseaseNum');

            $table->integer('DocNum');

            $table->integer('MountNum');

            $table->date('SecDateEntry');

            $table->string('SecDateTEdit');

        });

    }

    public function down()
    {
        Schema::dropIfExists('schoolapproval');
    }
};
