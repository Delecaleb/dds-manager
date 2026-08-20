<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('payconnectresponseweb', function (Blueprint $table) {

            $table->integer('PayConnectResponseWebNum');

            $table->integer('PatNum');

            $table->integer('PayNum');

            $table->string('AccountToken');

            $table->string('PaymentToken');

            $table->string('ProcessingStatus');

            $table->date('DateTimeEntry');

            $table->date('DateTimePending');

            $table->date('DateTimeCompleted');

            $table->date('DateTimeExpired');

            $table->date('DateTimeLastError');

            $table->text('LastResponseStr');

            $table->integer('CCSource');

            $table->string('Amount');

            $table->string('PayNote');

            $table->integer('IsTokenSaved');

            $table->string('PayToken');

            $table->string('ExpDateToken');

            $table->string('RefNumber');

            $table->string('TransType');

            $table->string('EmailResponse');

            $table->string('LogGuid');

        });

    }

    public function down()
    {
        Schema::dropIfExists('payconnectresponseweb');
    }
};
