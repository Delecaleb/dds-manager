<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('deposit', function (Blueprint $table) {

            $table->integer('DepositNum');

            $table->date('DateDeposit');

            $table->text('BankAccountInfo');

            $table->string('Amount');

            $table->string('Memo');

            $table->string('Batch');

            $table->integer('DepositAccountNum');

            $table->integer('IsSentToQuickBooksOnline');

        });

    }

    public function down()
    {
        Schema::dropIfExists('deposit');
    }
};
