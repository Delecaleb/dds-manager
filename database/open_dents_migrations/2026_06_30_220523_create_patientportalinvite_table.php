<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('patientportalinvite', function(Blueprint $table){

$table->integer('PatientPortalInviteNum');

$table->integer('PatNum');

$table->integer('ApptNum');

$table->integer('ClinicNum');

$table->date('DateTimeEntry');

$table->integer('TSPrior');

$table->integer('SendStatus');

$table->integer('MessageFk');

$table->text('ResponseDescript');

$table->integer('MessageType');

$table->date('DateTimeSent');

$table->integer('ApptReminderRuleNum');

$table->date('ApptDateTime');



});

}


public function down()
{
Schema::dropIfExists('patientportalinvite');
}

};
