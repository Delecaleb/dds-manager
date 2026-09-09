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
        Schema::create('od_statements', function (Blueprint $table) {
            $table->id();
            $table->integer('StatementNum')->index();
            $table->integer('PatNum')->nullable()->index();
            $table->dateTime('DateSent')->nullable()->index();
            $table->dateTime('DateRangeFrom')->nullable();
            $table->dateTime('DateRangeTo')->nullable();
            $table->text('Note')->nullable();
            $table->text('NoteBold')->nullable();
            $table->integer('Mode_')->nullable();
            $table->integer('HidePayment')->nullable();
            $table->integer('SinglePatient')->nullable();
            $table->integer('Intermingled')->nullable();
            $table->integer('IsSent')->nullable();
            $table->integer('DocNum')->nullable();
            $table->dateTime('DateTStamp')->nullable()->index();
            $table->integer('IsReceipt')->nullable();
            $table->integer('IsInvoice')->nullable();
            $table->integer('IsInvoiceCopy')->nullable();
            $table->text('EmailSubject')->nullable();
            $table->text('EmailBody')->nullable();
            $table->integer('SuperFamily')->nullable();
            $table->integer('IsBalValid')->nullable();
            $table->decimal('InsEst', 10, 2)->nullable();
            $table->decimal('BalTotal', 10, 2)->nullable();
            $table->string('StatementType')->nullable();
            $table->string('ShortGUID')->nullable();
            $table->string('StatementShortURL')->nullable();
            $table->text('StatementURL')->nullable();
            $table->integer('SmsSendStatus')->nullable();
            $table->integer('LimitedCustomFamily')->nullable();
            $table->unsignedBigInteger('office_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['office_id', 'StatementNum'], 'od_statements_office_statementnum_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_statements');
    }
};
