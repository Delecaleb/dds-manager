<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('employee', function (Blueprint $table) {

            $table->integer('EmployeeNum');

            $table->string('LName');

            $table->string('FName');

            $table->string('MiddleI');

            $table->integer('IsHidden');

            $table->string('ClockStatus');

            $table->integer('PhoneExt');

            $table->string('PayrollID');

            $table->string('WirelessPhone');

            $table->string('EmailWork');

            $table->string('EmailPersonal');

            $table->integer('IsFurloughed');

            $table->integer('IsWorkingHome');

            $table->integer('ReportsTo');

            $table->integer('Title');

        });

    }

    public function down()
    {
        Schema::dropIfExists('employee');
    }
};
