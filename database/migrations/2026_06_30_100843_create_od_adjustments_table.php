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
            $table->string('AdjNum');
            $table->string('AdjDate');
            $table->string('AdjAmt');
            $table->string('PatNum');
            $table->string('AdjType');
            $table->string('ProvNum');
            $table->string('AdjNote');
            $table->string('ProcDate');
            $table->string('ProcNum');
            $table->string('DateEntry');
            $table->string('ClinicNum');
            $table->string('StatementNum');
            $table->string('SecUserNumEntry');
            $table->string('SecDateTEdit');
            $table->string('TaxTransID');
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
