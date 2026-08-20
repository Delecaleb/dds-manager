<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('periomeasure', function (Blueprint $table) {

            $table->integer('PerioMeasureNum');

            $table->integer('PerioExamNum');

            $table->integer('SequenceType');

            $table->integer('IntTooth');

            $table->integer('ToothValue');

            $table->integer('MBvalue');

            $table->integer('Bvalue');

            $table->integer('DBvalue');

            $table->integer('MLvalue');

            $table->integer('Lvalue');

            $table->integer('DLvalue');

            $table->date('SecDateTEntry');

            $table->string('SecDateTEdit');

        });

    }

    public function down()
    {
        Schema::dropIfExists('periomeasure');
    }
};
