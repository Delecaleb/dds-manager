<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('xchargetransaction', function (Blueprint $table) {

            $table->integer('XChargeTransactionNum');

            $table->string('TransType');

            $table->string('Amount');

            $table->string('CCEntry');

            $table->integer('PatNum');

            $table->string('Result');

            $table->string('ClerkID');

            $table->string('ResultCode');

            $table->string('Expiration');

            $table->string('CCType');

            $table->string('CreditCardNum');

            $table->string('BatchNum');

            $table->string('ItemNum');

            $table->string('ApprCode');

            $table->date('TransactionDateTime');

            $table->string('BatchTotal');

        });

    }

    public function down()
    {
        Schema::dropIfExists('xchargetransaction');
    }
};
