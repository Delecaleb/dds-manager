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
        Schema::create('od_patient_balances', function (Blueprint $table) {
            $table->id();
            $table->string('PatNum')->nullable();
            $table->decimal('Bal_0_30', 10, 2)->default(0);
            $table->decimal('Bal_31_60', 10, 2)->default(0);
            $table->decimal('Bal_61_90', 10, 2)->default(0);
            $table->decimal('BalOver90', 10, 2)->default(0);

            $table->decimal('Total', 10, 2)->default(0);

            $table->decimal('InsEst', 10, 2)->default(0);

            $table->decimal('EstBal', 10, 2)->default(0);

            $table->decimal('PatEstBal', 10, 2)->default(0);

            $table->decimal('Unearned', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_patient_balances');
    }
};
