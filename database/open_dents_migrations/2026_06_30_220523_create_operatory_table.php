<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('operatory', function (Blueprint $table) {

            $table->integer('OperatoryNum');

            $table->string('OpName');

            $table->string('Abbrev');

            $table->integer('ItemOrder');

            $table->integer('IsHidden');

            $table->integer('ProvDentist');

            $table->integer('ProvHygienist');

            $table->integer('IsHygiene');

            $table->integer('ClinicNum');

            $table->string('DateTStamp');

            $table->integer('SetProspective');

            $table->integer('IsWebSched');

            $table->integer('IsNewPatAppt');

            $table->integer('OperatoryType');

        });

    }

    public function down()
    {
        Schema::dropIfExists('operatory');
    }
};
