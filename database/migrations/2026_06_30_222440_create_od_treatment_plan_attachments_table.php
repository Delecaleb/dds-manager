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
        Schema::create('od_treatment_plan_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('TreatPlanAttachNum')->nullable();

            $table->string('TreatPlanNum')->nullable();

            $table->string('ProcNum')->nullable();

            $table->string('Priority')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_treatment_plan_attachments');
    }
};
