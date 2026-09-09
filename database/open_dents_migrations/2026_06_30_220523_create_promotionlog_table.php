<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('promotionlog', function (Blueprint $table) {

            $table->integer('PromotionLogNum');

            $table->integer('PromotionNum');

            $table->integer('PatNum');

            $table->integer('MessageFk');

            $table->integer('EmailHostingFK');

            $table->date('DateTimeSent');

            $table->integer('PromotionStatus');

            $table->integer('ClinicNum');

            $table->integer('SendStatus');

            $table->integer('MessageType');

            $table->date('DateTimeEntry');

            $table->text('ResponseDescript');

            $table->integer('ApptReminderRuleNum');

        });

    }

    public function down()
    {
        Schema::dropIfExists('promotionlog');
    }
};
