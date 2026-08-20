<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('chartview', function (Blueprint $table) {

            $table->integer('ChartViewNum');

            $table->string('Description');

            $table->integer('ItemOrder');

            $table->integer('ProcStatuses');

            $table->integer('ObjectTypes');

            $table->integer('ShowProcNotes');

            $table->integer('IsAudit');

            $table->integer('SelectedTeethOnly');

            $table->integer('OrionStatusFlags');

            $table->integer('DatesShowing');

            $table->integer('IsTpCharting');

        });

    }

    public function down()
    {
        Schema::dropIfExists('chartview');
    }
};
