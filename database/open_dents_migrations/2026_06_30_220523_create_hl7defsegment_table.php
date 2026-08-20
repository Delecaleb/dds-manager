<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('hl7defsegment', function (Blueprint $table) {

            $table->integer('HL7DefSegmentNum');

            $table->integer('HL7DefMessageNum');

            $table->integer('ItemOrder');

            $table->integer('CanRepeat');

            $table->integer('IsOptional');

            $table->string('SegmentName');

            $table->text('Note');

        });

    }

    public function down()
    {
        Schema::dropIfExists('hl7defsegment');
    }
};
