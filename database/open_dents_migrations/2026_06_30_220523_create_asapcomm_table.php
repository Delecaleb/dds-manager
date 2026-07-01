<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('asapcomm', function(Blueprint $table){

$table->integer('AsapCommNum');

$table->integer('FKey');

$table->integer('FKeyType');

$table->integer('ScheduleNum');

$table->integer('PatNum');

$table->integer('ClinicNum');

$table->string('ShortGUID');

$table->date('DateTimeEntry');

$table->date('DateTimeExpire');

$table->date('DateTimeSmsScheduled');

$table->integer('SmsSendStatus');

$table->integer('EmailSendStatus');

$table->date('DateTimeSmsSent');

$table->date('DateTimeEmailSent');

$table->integer('EmailMessageNum');

$table->integer('ResponseStatus');

$table->date('DateTimeOrig');

$table->text('TemplateText');

$table->text('TemplateEmail');

$table->string('TemplateEmailSubj');

$table->text('Note');

$table->text('GuidMessageToMobile');

$table->string('EmailTemplateType');

$table->integer('UserNum');



});

}


public function down()
{
Schema::dropIfExists('asapcomm');
}

};
