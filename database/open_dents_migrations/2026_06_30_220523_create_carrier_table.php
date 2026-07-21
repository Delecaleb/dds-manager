<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {

    public function up()
    {

        Schema::create('carrier', function (Blueprint $table) {

            $table->integer('CarrierNum');

            $table->string('CarrierName');

            $table->string('Address');

            $table->string('Address2');

            $table->string('City');

            $table->string('State');

            $table->string('Zip');

            $table->string('Phone');

            $table->string('ElectID');

            $table->integer('NoSendElect');

            $table->integer('IsCDA');

            $table->string('CDAnetVersion');

            $table->integer('CanadianNetworkNum');

            $table->integer('IsHidden');

            $table->integer('CanadianEncryptionMethod');

            $table->integer('CanadianSupportedTypes');

            $table->integer('SecUserNumEntry');

            $table->date('SecDateEntry');

            $table->string('SecDateTEdit');

            $table->string('TIN');

            $table->integer('CarrierGroupName');

            $table->integer('ApptTextBackColor');

            $table->integer('IsCoinsuranceInverted');

            $table->integer('TrustedEtransFlags');

            $table->integer('CobInsPaidBehaviorOverride');

            $table->integer('EraAutomationOverride');

            $table->integer('OrthoInsPayConsolidate');

            $table->integer('PaySuiteTransSup');

            $table->text('PreAuthCodes');



        });

    }


    public function down()
    {
        Schema::dropIfExists('carrier');
    }

};
