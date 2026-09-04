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
        Schema::create('od_recalls', function (Blueprint $table) {
            $table->id();
            $table->integer('RecallNum')->nullable();
            $table->integer('PatNum')->nullable();
            $table->date('DateDueCalc')->nullable();
            $table->date('DateDue')->nullable();
            $table->date('DatePrevious')->nullable();
            $table->integer('RecallInterval')->nullable();
            $table->integer('RecallStatus')->nullable();
            $table->text('Note')->nullable();
            $table->integer('IsDisabled')->nullable();
            $table->string('DateTStamp')->nullable();
            $table->integer('RecallTypeNum')->nullable();
            $table->string('DisableUntilBalance')->nullable();
            $table->date('DisableUntilDate')->nullable();
            $table->date('DateScheduled')->nullable();
            $table->integer('Priority')->nullable();
            $table->string('TimePatternOverride')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_recalls');
    }
};
