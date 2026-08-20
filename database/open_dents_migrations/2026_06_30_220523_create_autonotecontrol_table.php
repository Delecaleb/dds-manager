<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('autonotecontrol', function (Blueprint $table) {

            $table->integer('AutoNoteControlNum');

            $table->string('Descript');

            $table->string('ControlType');

            $table->string('ControlLabel');

            $table->text('ControlOptions');

        });

    }

    public function down()
    {
        Schema::dropIfExists('autonotecontrol');
    }
};
