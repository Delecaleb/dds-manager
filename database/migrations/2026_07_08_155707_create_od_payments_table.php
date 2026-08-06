<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('od_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('PayNum')->nullable();

            $table->integer('PayType')->nullable();

            $table->date('PayDate')->nullable();

            $table->string('PayAmt')->nullable();

            $table->string('CheckNum')->nullable();

            $table->string('BankBranch')->nullable();

            $table->text('PayNote')->nullable();

            $table->integer('IsSplit')->nullable();

            $table->integer('PatNum')->nullable();

            $table->integer('ClinicNum')->nullable();

            $table->date('DateEntry')->nullable();

            $table->integer('DepositNum')->nullable();

            $table->text('Receipt')->nullable();

            $table->integer('IsRecurringCC')->nullable();

            $table->integer('SecUserNumEntry')->nullable();

            $table->string('SecDateTEdit')->nullable();

            $table->integer('PaymentSource')->nullable();

            $table->integer('ProcessStatus')->nullable();

            $table->date('RecurringChargeDate')->nullable();

            $table->string('ExternalId')->nullable();

            $table->integer('PaymentStatus')->nullable();

            $table->integer('IsCcCompleted')->nullable();

            $table->string('MerchantFee')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_payments');
    }
};
