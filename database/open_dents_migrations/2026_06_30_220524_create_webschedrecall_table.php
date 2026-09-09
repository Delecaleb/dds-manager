<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('webschedrecall', function (Blueprint $table) {

            $table->integer('WebSchedRecallNum');

            $table->integer('ClinicNum');

            $table->integer('PatNum');

            $table->integer('RecallNum');

            $table->date('DateTimeEntry');

            $table->date('DateDue');

            $table->integer('ReminderCount');

            $table->date('DateTimeSent');

            $table->date('DateTimeSendFailed');

            $table->integer('SendStatus');

            $table->string('ShortGUID');

            $table->text('ResponseDescript');

            $table->integer('Source');

            $table->integer('CommlogNum');

            $table->integer('MessageType');

            $table->integer('MessageFk');

            $table->integer('ApptReminderRuleNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('webschedrecall');
    }
};
