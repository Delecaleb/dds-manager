<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('medicationpat', function (Blueprint $table) {

            $table->integer('MedicationPatNum');

            $table->integer('PatNum');

            $table->integer('MedicationNum');

            $table->text('PatNote');

            $table->string('DateTStamp');

            $table->date('DateStart');

            $table->date('DateStop');

            $table->integer('ProvNum');

            $table->string('MedDescript');

            $table->integer('RxCui');

            $table->string('ErxGuid');

            $table->integer('IsCpoe');

        });

    }

    public function down()
    {
        Schema::dropIfExists('medicationpat');
    }
};
