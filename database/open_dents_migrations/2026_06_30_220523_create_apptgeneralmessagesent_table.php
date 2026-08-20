<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('apptgeneralmessagesent', function (Blueprint $table) {

            $table->integer('ApptGeneralMessageSentNum');

            $table->integer('ApptNum');

            $table->integer('PatNum');

            $table->integer('ClinicNum');

            $table->date('DateTimeEntry');

            $table->integer('TSPrior');

            $table->integer('ApptReminderRuleNum');

            $table->integer('SendStatus');

            $table->date('ApptDateTime');

            $table->integer('MessageType');

            $table->integer('MessageFk');

            $table->date('DateTimeSent');

            $table->text('ResponseDescript');

        });

    }

    public function down()
    {
        Schema::dropIfExists('apptgeneralmessagesent');
    }
};
