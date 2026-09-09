<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('feeschednote', function (Blueprint $table) {

            $table->integer('FeeSchedNoteNum');

            $table->integer('FeeSchedNum');

            $table->text('ClinicNums');

            $table->text('Note');

            $table->date('DateEntry');

            $table->integer('SecUserNumEntry');

            $table->date('SecDateEntry');

            $table->string('SecDateTEdit');

        });

    }

    public function down()
    {
        Schema::dropIfExists('feeschednote');
    }
};
