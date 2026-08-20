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
        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('TreatPlanNum')->nullable();

            $table->string('PatNum')->nullable();

            $table->date('DateTP')->nullable();

            $table->string('Heading')->nullable();

            $table->text('Note')->nullable();

            $table->text('Signature')->nullable();

            $table->string('SigIsTopaz')->nullable();

            $table->string('ResponsParty')->nullable();

            $table->string('DocNum')->nullable();

            $table->string('TPStatus')->nullable();

            $table->string('SecUserNumEntry')->nullable();

            $table->date('SecDateEntry')->nullable();

            $table->string('SecDateTEdit')->nullable();

            $table->string('UserNumPresenter')->nullable();

            $table->string('TPType')->nullable();

            $table->text('SignaturePractice')->nullable();

            $table->date('DateTSigned')->nullable();

            $table->date('DateTPracticeSigned')->nullable();

            $table->string('SignatureText')->nullable();

            $table->string('SignaturePracticeText')->nullable();

            $table->string('MobileAppDeviceNum')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_treatment_plans');
    }
};
