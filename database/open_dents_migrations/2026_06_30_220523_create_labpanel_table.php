<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('labpanel', function (Blueprint $table) {

            $table->integer('LabPanelNum');

            $table->integer('PatNum');

            $table->text('RawMessage');

            $table->string('LabNameAddress');

            $table->string('DateTStamp');

            $table->string('SpecimenCondition');

            $table->string('SpecimenSource');

            $table->string('ServiceId');

            $table->string('ServiceName');

            $table->integer('MedicalOrderNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('labpanel');
    }
};
