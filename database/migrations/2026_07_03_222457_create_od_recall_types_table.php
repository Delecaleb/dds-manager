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
        Schema::create('od_recall_types', function (Blueprint $table) {
            $table->id();
            $table->integer('RecallTypeNum')->nullable();
            $table->string('Description')->nullable();
            $table->integer('DefaultInterval')->nullable();
            $table->string('TimePattern')->nullable();
            $table->string('Procedures')->nullable();
            $table->integer('AppendToSpecial')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_recall_types');
    }
};
