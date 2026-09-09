<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('labcase', function (Blueprint $table) {

            $table->integer('LabCaseNum');

            $table->integer('PatNum');

            $table->integer('LaboratoryNum');

            $table->integer('AptNum');

            $table->integer('PlannedAptNum');

            $table->date('DateTimeDue');

            $table->date('DateTimeCreated');

            $table->date('DateTimeSent');

            $table->date('DateTimeRecd');

            $table->date('DateTimeChecked');

            $table->integer('ProvNum');

            $table->text('Instructions');

            $table->string('LabFee');

            $table->string('DateTStamp');

            $table->string('InvoiceNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('labcase');
    }
};
