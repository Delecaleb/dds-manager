<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('insverifyhist', function (Blueprint $table) {

            $table->integer('InsVerifyHistNum');

            $table->integer('InsVerifyNum');

            $table->date('DateLastVerified');

            $table->integer('UserNum');

            $table->integer('VerifyType');

            $table->integer('FKey');

            $table->integer('DefNum');

            $table->text('Note');

            $table->date('DateLastAssigned');

            $table->date('DateTimeEntry');

            $table->string('HoursAvailableForVerification');

            $table->integer('VerifyUserNum');

            $table->string('SecDateTEdit');

            $table->string('SecurityHash');

        });

    }

    public function down()
    {
        Schema::dropIfExists('insverifyhist');
    }
};
