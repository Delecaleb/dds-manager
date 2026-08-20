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
        Schema::create('od_definitions', function (Blueprint $table) {
            $table->id();
            $table->integer('DefNum')->nullable();

            $table->integer('Category')->nullable();

            $table->integer('ItemOrder')->nullable();

            $table->string('ItemName')->nullable();

            $table->string('ItemValue')->nullable();

            $table->integer('ItemColor')->nullable();

            $table->integer('IsHidden')->nullable();

            $table->string('Supp')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_definitions');
    }
};
