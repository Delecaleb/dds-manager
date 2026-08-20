<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('schedule', function (Blueprint $table) {

            $table->integer('ScheduleNum');

            $table->date('SchedDate');

            $table->string('StartTime');

            $table->string('StopTime');

            $table->integer('SchedType');

            $table->integer('ProvNum');

            $table->integer('BlockoutType');

            $table->text('Note');

            $table->integer('Status');

            $table->integer('EmployeeNum');

            $table->string('DateTStamp');

            $table->integer('ClinicNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('schedule');
    }
};
