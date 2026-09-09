<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('apptnewpatthankyousent', function (Blueprint $table) {

            $table->integer('ApptNewPatThankYouSentNum');

            $table->integer('ApptNum');

            $table->date('ApptDateTime');

            $table->date('ApptSecDateTEntry');

            $table->integer('TSPrior');

            $table->integer('ApptReminderRuleNum');

            $table->integer('ClinicNum');

            $table->integer('PatNum');

            $table->text('ResponseDescript');

            $table->date('DateTimeNewPatThankYouTransmit');

            $table->string('ShortGUID');

            $table->integer('SendStatus');

            $table->integer('MessageType');

            $table->integer('MessageFk');

            $table->date('DateTimeEntry');

            $table->date('DateTimeSent');

        });

    }

    public function down()
    {
        Schema::dropIfExists('apptnewpatthankyousent');
    }
};
