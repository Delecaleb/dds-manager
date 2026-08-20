<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('labresult', function (Blueprint $table) {

            $table->integer('LabResultNum');

            $table->integer('LabPanelNum');

            $table->date('DateTimeTest');

            $table->string('TestName');

            $table->string('DateTStamp');

            $table->string('TestID');

            $table->string('ObsValue');

            $table->string('ObsUnits');

            $table->string('ObsRange');

            $table->integer('AbnormalFlag');

        });

    }

    public function down()
    {
        Schema::dropIfExists('labresult');
    }
};
