<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('timecardrule', function (Blueprint $table) {

            $table->integer('TimeCardRuleNum');

            $table->integer('EmployeeNum');

            $table->string('OverHoursPerDay');

            $table->string('AfterTimeOfDay');

            $table->string('BeforeTimeOfDay');

            $table->integer('IsOvertimeExempt');

            $table->string('MinClockInTime');

            $table->integer('HasWeekendRate3');

        });

    }

    public function down()
    {
        Schema::dropIfExists('timecardrule');
    }
};
