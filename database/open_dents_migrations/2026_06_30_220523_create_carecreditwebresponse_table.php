<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('carecreditwebresponse', function(Blueprint $table){

$table->integer('CareCreditWebResponseNum');

$table->integer('PatNum');

$table->integer('PayNum');

$table->string('RefNumber');

$table->string('Amount');

$table->string('WebToken');

$table->string('ProcessingStatus');

$table->date('DateTimeEntry');

$table->date('DateTimePending');

$table->date('DateTimeCompleted');

$table->date('DateTimeExpired');

$table->date('DateTimeLastError');

$table->text('LastResponseStr');

$table->integer('ClinicNum');

$table->string('ServiceType');

$table->string('TransType');

$table->string('MerchantNumber');

$table->integer('HasLogged');



});

}


public function down()
{
Schema::dropIfExists('carecreditwebresponse');
}

};
