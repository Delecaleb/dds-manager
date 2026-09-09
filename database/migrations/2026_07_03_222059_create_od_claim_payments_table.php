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
        Schema::create('od_claim_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('ClaimPaymentNum')->nullable();
            $table->date('CheckDate')->nullable();
            $table->string('CheckAmt')->nullable();
            $table->string('CheckNum')->nullable();
            $table->string('BankBranch')->nullable();
            $table->text('Note')->nullable();
            $table->integer('ClinicNum')->nullable();
            $table->integer('DepositNum')->nullable();
            $table->string('CarrierName')->nullable();
            $table->date('DateIssued')->nullable();
            $table->integer('IsPartial')->nullable();
            $table->integer('PayType')->nullable();
            $table->integer('SecUserNumEntry')->nullable();
            $table->date('SecDateEntry')->nullable();
            $table->string('SecDateTEdit')->nullable();
            $table->integer('PayGroup')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_claim_payments');
    }
};
