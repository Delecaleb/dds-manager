<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('programproperty', function (Blueprint $table) {

            $table->integer('ProgramPropertyNum');

            $table->integer('ProgramNum');

            $table->string('PropertyDesc');

            $table->text('PropertyValue');

            $table->string('ComputerName');

            $table->integer('ClinicNum');

            $table->integer('IsMasked');

            $table->integer('IsHighSecurity');

        });

    }

    public function down()
    {
        Schema::dropIfExists('programproperty');
    }
};
