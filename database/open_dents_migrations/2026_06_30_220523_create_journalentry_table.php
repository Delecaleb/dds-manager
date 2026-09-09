<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('journalentry', function (Blueprint $table) {

            $table->integer('JournalEntryNum');

            $table->integer('TransactionNum');

            $table->integer('AccountNum');

            $table->date('DateDisplayed');

            $table->string('DebitAmt');

            $table->string('CreditAmt');

            $table->text('Memo');

            $table->text('Splits');

            $table->string('CheckNumber');

            $table->integer('ReconcileNum');

            $table->integer('SecUserNumEntry');

            $table->date('SecDateTEntry');

            $table->integer('SecUserNumEdit');

            $table->string('SecDateTEdit');

            $table->string('Payee');

            $table->text('Notes');

        });

    }

    public function down()
    {
        Schema::dropIfExists('journalentry');
    }
};
