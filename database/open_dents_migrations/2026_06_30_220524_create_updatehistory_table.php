<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('updatehistory', function (Blueprint $table) {

            $table->integer('UpdateHistoryNum');

            $table->date('DateTimeUpdated');

            $table->string('ProgramVersion');

            $table->text('Signature');

        });

    }

    public function down()
    {
        Schema::dropIfExists('updatehistory');
    }
};
