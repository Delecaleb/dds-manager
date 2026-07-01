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
        Schema::create('od_appointments', function (Blueprint $table) {
            $table->id();
            $table->string('AptNum')->nullable();

            $table->string('PatNum')->nullable();

            $table->string('AptStatus')->nullable();

            $table->string('Pattern')->nullable();

            $table->string('Confirmed')->nullable();

            $table->string('TimeLocked')->nullable();

            $table->string('Op')->nullable();

            $table->text('Note')->nullable();

            $table->string('ProvNum')->nullable();

            $table->string('ProvHyg')->nullable();

            $table->string('AptDateTime')->nullable();

            $table->string('NextAptNum')->nullable();

            $table->string('UnschedStatus')->nullable();

            $table->string('IsNewPatient')->nullable();

            $table->text('ProcDescript')->nullable();

            $table->string('Assistant')->nullable();

            $table->string('ClinicNum')->nullable();

            $table->string('IsHygiene')->nullable();

            $table->string('DateTStamp')->nullable();

            $table->string('DateTimeArrived')->nullable();

            $table->string('DateTimeSeated')->nullable();

            $table->string('DateTimeDismissed')->nullable();

            $table->string('InsPlan1')->nullable();

            $table->string('InsPlan2')->nullable();

            $table->string('DateTimeAskedToArrive')->nullable();

            $table->text('ProcsColored')->nullable();

            $table->string('ColorOverride')->nullable();

            $table->string('AppointmentTypeNum')->nullable();

            $table->string('SecUserNumEntry')->nullable();

            $table->string('SecDateTEntry')->nullable();

            $table->string('Priority')->nullable();

            $table->string('ProvBarText')->nullable();

            $table->string('PatternSecondary')->nullable();

            $table->string('SecurityHash')->nullable();

            $table->string('ItemOrderPlanned')->nullable();

            $table->string('IsMirrored')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
