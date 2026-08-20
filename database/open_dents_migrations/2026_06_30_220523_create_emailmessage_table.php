<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('emailmessage', function (Blueprint $table) {

            $table->integer('EmailMessageNum');

            $table->integer('PatNum');

            $table->text('ToAddress');

            $table->text('FromAddress');

            $table->text('Subject');

            $table->text('BodyText');

            $table->date('MsgDateTime');

            $table->integer('SentOrReceived');

            $table->string('RecipientAddress');

            $table->text('RawEmailIn');

            $table->integer('ProvNumWebMail');

            $table->integer('PatNumSubj');

            $table->text('CcAddress');

            $table->text('BccAddress');

            $table->integer('HideIn');

            $table->integer('AptNum');

            $table->integer('UserNum');

            $table->integer('HtmlType');

            $table->date('SecDateTEntry');

            $table->string('SecDateTEdit');

            $table->string('MsgType');

            $table->text('FailReason');

        });

    }

    public function down()
    {
        Schema::dropIfExists('emailmessage');
    }
};
