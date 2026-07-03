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
        Schema::create('od_adjustments', function (Blueprint $table) {
            $table->id();
            $table->integer('AdjNum')->nullable();

            $table->date('AdjDate')->nullable();

            $table->string('AdjAmt')->nullable();

            $table->integer('PatNum')->nullable();

            $table->integer('AdjType')->nullable();

            $table->integer('ProvNum')->nullable();

            $table->text('AdjNote')->nullable();

            $table->date('ProcDate')->nullable();

            $table->integer('ProcNum')->nullable();

            $table->date('DateEntry')->nullable();

            $table->integer('ClinicNum')->nullable();

            $table->integer('StatementNum')->nullable();

            $table->integer('SecUserNumEntry')->nullable();

            $table->string('SecDateTEdit')->nullable();

            $table->integer('TaxTransID')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_adjustments');
    }
};
