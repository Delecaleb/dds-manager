<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('etrans', function (Blueprint $table) {

            $table->integer('EtransNum');

            $table->date('DateTimeTrans');

            $table->integer('ClearingHouseNum');

            $table->integer('Etype');

            $table->integer('ClaimNum');

            $table->integer('OfficeSequenceNumber');

            $table->integer('CarrierTransCounter');

            $table->integer('CarrierTransCounter2');

            $table->integer('CarrierNum');

            $table->integer('CarrierNum2');

            $table->integer('PatNum');

            $table->integer('BatchNumber');

            $table->string('AckCode');

            $table->integer('TransSetNum');

            $table->text('Note');

            $table->integer('EtransMessageTextNum');

            $table->integer('AckEtransNum');

            $table->integer('PlanNum');

            $table->integer('InsSubNum');

            $table->string('TranSetId835');

            $table->string('CarrierNameRaw');

            $table->string('PatientNameRaw');

            $table->integer('UserNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('etrans');
    }
};
