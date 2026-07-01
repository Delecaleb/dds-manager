<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('apptremindersent', function(Blueprint $table){

$table->integer('ApptReminderSentNum');

$table->integer('ApptNum');

$table->date('ApptDateTime');

$table->date('DateTimeSent');

$table->integer('TSPrior');

$table->integer('ApptReminderRuleNum');

$table->integer('PatNum');

$table->integer('ClinicNum');

$table->integer('SendStatus');

$table->integer('MessageType');

$table->integer('MessageFk');

$table->date('DateTimeEntry');

$table->text('ResponseDescript');



});

}


public function down()
{
Schema::dropIfExists('apptremindersent');
}

};
