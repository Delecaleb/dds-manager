<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('transaction', function (Blueprint $table) {

            $table->integer('TransactionNum');

            $table->date('DateTimeEntry');

            $table->integer('UserNum');

            $table->integer('DepositNum');

            $table->integer('PayNum');

            $table->integer('SecUserNumEdit');

            $table->string('SecDateTEdit');

            $table->integer('TransactionInvoiceNum');

            $table->integer('NeedsReview');

        });

    }

    public function down()
    {
        Schema::dropIfExists('transaction');
    }
};
