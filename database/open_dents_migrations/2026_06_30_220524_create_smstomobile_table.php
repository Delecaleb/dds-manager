<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('smstomobile', function(Blueprint $table){

$table->integer('SmsToMobileNum');

$table->integer('PatNum');

$table->string('GuidMessage');

$table->string('GuidBatch');

$table->string('SmsPhoneNumber');

$table->string('MobilePhoneNumber');

$table->integer('IsTimeSensitive');

$table->integer('MsgType');

$table->text('MsgText');

$table->integer('SmsStatus');

$table->integer('MsgParts');

$table->string('MsgChargeUSD');

$table->integer('ClinicNum');

$table->string('CustErrorText');

$table->date('DateTimeSent');

$table->date('DateTimeTerminated');

$table->integer('IsHidden');

$table->string('MsgDiscountUSD');

$table->string('SecDateTEdit');

$table->integer('SecUserNumEntry');



});

}


public function down()
{
Schema::dropIfExists('smstomobile');
}

};
