<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('smsfrommobile', function(Blueprint $table){

$table->integer('SmsFromMobileNum');

$table->integer('PatNum');

$table->integer('ClinicNum');

$table->integer('CommlogNum');

$table->text('MsgText');

$table->date('DateTimeReceived');

$table->string('SmsPhoneNumber');

$table->string('MobilePhoneNumber');

$table->integer('MsgPart');

$table->integer('MsgTotal');

$table->string('MsgRefID');

$table->integer('SmsStatus');

$table->string('Flags');

$table->integer('IsHidden');

$table->integer('MatchCount');

$table->string('GuidMessage');

$table->string('SecDateTEdit');



});

}


public function down()
{
Schema::dropIfExists('smsfrommobile');
}

};
