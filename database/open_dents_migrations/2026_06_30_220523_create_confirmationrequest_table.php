<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('confirmationrequest', function (Blueprint $table) {

            $table->integer('ConfirmationRequestNum');

            $table->integer('ClinicNum');

            $table->integer('PatNum');

            $table->integer('ApptNum');

            $table->date('DateTimeConfirmExpire');

            $table->string('ShortGUID');

            $table->string('ConfirmCode');

            $table->date('DateTimeEntry');

            $table->date('DateTimeConfirmTransmit');

            $table->date('DateTimeRSVP');

            $table->integer('RSVPStatus');

            $table->text('ResponseDescript');

            $table->text('GuidMessageFromMobile');

            $table->date('ApptDateTime');

            $table->integer('TSPrior');

            $table->integer('DoNotResend');

            $table->integer('SendStatus');

            $table->integer('ApptReminderRuleNum');

            $table->integer('MessageType');

            $table->integer('MessageFk');

            $table->date('DateTimeSent');

        });

    }

    public function down()
    {
        Schema::dropIfExists('confirmationrequest');
    }
};
