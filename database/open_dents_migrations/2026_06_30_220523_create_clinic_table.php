<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('clinic', function(Blueprint $table){

$table->integer('ClinicNum');

$table->string('Description');

$table->string('Address');

$table->string('Address2');

$table->string('City');

$table->string('State');

$table->string('Zip');

$table->string('Phone');

$table->string('BankNumber');

$table->integer('DefaultPlaceService');

$table->integer('InsBillingProv');

$table->string('Fax');

$table->integer('EmailAddressNum');

$table->integer('DefaultProv');

$table->date('SmsContractDate');

$table->string('SmsMonthlyLimit');

$table->integer('IsMedicalOnly');

$table->string('BillingAddress');

$table->string('BillingAddress2');

$table->string('BillingCity');

$table->string('BillingState');

$table->string('BillingZip');

$table->string('PayToAddress');

$table->string('PayToAddress2');

$table->string('PayToCity');

$table->string('PayToState');

$table->string('PayToZip');

$table->integer('UseBillAddrOnClaims');

$table->integer('Region');

$table->integer('ItemOrder');

$table->integer('IsInsVerifyExcluded');

$table->string('Abbr');

$table->string('MedLabAccountNum');

$table->integer('IsConfirmEnabled');

$table->integer('IsConfirmDefault');

$table->integer('IsNewPatApptExcluded');

$table->integer('IsHidden');

$table->integer('ExternalID');

$table->string('SchedNote');

$table->integer('HasProcOnRx');

$table->string('TimeZone');

$table->string('EmailAliasOverride');



});

}


public function down()
{
Schema::dropIfExists('clinic');
}

};
