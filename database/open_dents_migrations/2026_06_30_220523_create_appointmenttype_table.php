<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('appointmenttype', function (Blueprint $table) {

            $table->integer('AppointmentTypeNum');

            $table->string('AppointmentTypeName');

            $table->integer('AppointmentTypeColor');

            $table->integer('ItemOrder');

            $table->integer('IsHidden');

            $table->string('Pattern');

            $table->string('CodeStr');

            $table->string('CodeStrRequired');

            $table->integer('RequiredProcCodesNeeded');

            $table->string('BlockoutTypes');

        });

    }

    public function down()
    {
        Schema::dropIfExists('appointmenttype');
    }
};
