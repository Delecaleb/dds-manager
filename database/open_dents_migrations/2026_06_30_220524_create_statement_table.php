<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('statement', function (Blueprint $table) {

            $table->integer('StatementNum');

            $table->integer('PatNum');

            $table->date('DateSent');

            $table->date('DateRangeFrom');

            $table->date('DateRangeTo');

            $table->text('Note');

            $table->text('NoteBold');

            $table->integer('Mode_');

            $table->integer('HidePayment');

            $table->integer('SinglePatient');

            $table->integer('Intermingled');

            $table->integer('IsSent');

            $table->integer('DocNum');

            $table->string('DateTStamp');

            $table->integer('IsReceipt');

            $table->integer('IsInvoice');

            $table->integer('IsInvoiceCopy');

            $table->string('EmailSubject');

            $table->text('EmailBody');

            $table->integer('SuperFamily');

            $table->integer('IsBalValid');

            $table->string('InsEst');

            $table->string('BalTotal');

            $table->string('StatementType');

            $table->string('ShortGUID');

            $table->string('StatementShortURL');

            $table->string('StatementURL');

            $table->integer('SmsSendStatus');

            $table->integer('LimitedCustomFamily');

            $table->integer('ShowTransSinceBalZero');

            $table->integer('IsServiceDateView');

        });

    }

    public function down()
    {
        Schema::dropIfExists('statement');
    }
};
