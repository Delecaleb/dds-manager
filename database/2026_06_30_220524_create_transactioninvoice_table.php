<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('transactioninvoice', function (Blueprint $table) {

            $table->integer('TransactionInvoiceNum');

            $table->string('FileName');

            $table->text('InvoiceData');

            $table->string('FilePath');

        });

    }

    public function down()
    {
        Schema::dropIfExists('transactioninvoice');
    }
};
