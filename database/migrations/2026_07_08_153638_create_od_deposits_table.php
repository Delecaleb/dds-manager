<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('od_deposits', function (Blueprint $table) {
            $table->id();
            $table->integer('DepositNum');

            $table->date('DateDeposit')->nullable();

            $table->text('BankAccountInfo')->nullable();

            $table->string('Amount')->nullable();

            $table->string('Memo')->nullable();

            $table->string('Batch')->nullable();

            $table->integer('DepositAccountNum')->nullable();

            $table->integer('IsSentToQuickBooksOnline')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_deposits');
    }
};
