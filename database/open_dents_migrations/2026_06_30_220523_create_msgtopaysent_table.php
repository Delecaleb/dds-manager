<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('msgtopaysent', function(Blueprint $table){

$table->integer('MsgToPaySentNum');

$table->integer('PatNum');

$table->integer('ClinicNum');

$table->integer('SendStatus');

$table->integer('Source');

$table->integer('MessageType');

$table->integer('MessageFk');

$table->text('Subject');

$table->text('Message');

$table->integer('EmailType');

$table->date('DateTimeEntry');

$table->date('DateTimeSent');

$table->text('ResponseDescript');

$table->integer('ApptReminderRuleNum');

$table->string('ShortGUID');

$table->date('DateTimeSendFailed');

$table->integer('ApptNum');

$table->date('ApptDateTime');

$table->integer('TSPrior');

$table->integer('StatementNum');



});

}


public function down()
{
Schema::dropIfExists('msgtopaysent');
}

};
