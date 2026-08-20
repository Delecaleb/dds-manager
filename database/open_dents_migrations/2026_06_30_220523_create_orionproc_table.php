<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('orionproc', function (Blueprint $table) {

            $table->integer('OrionProcNum');

            $table->integer('ProcNum');

            $table->integer('DPC');

            $table->date('DateScheduleBy');

            $table->date('DateStopClock');

            $table->integer('Status2');

            $table->integer('IsOnCall');

            $table->integer('IsEffectiveComm');

            $table->integer('IsRepair');

            $table->integer('DPCpost');

        });

    }

    public function down()
    {
        Schema::dropIfExists('orionproc');
    }
};
