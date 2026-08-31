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
        Schema::create('od_histappointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('office_id')->nullable()->index();
            $table->bigInteger('HistApptNum')->index();
            $table->bigInteger('HistUserNum')->nullable();
            $table->dateTime('HistDateTStamp')->nullable()->index();
            $table->integer('HistApptAction')->nullable();
            $table->integer('ApptSource')->nullable();
            $table->bigInteger('AptNum')->nullable()->index();
            $table->bigInteger('PatNum')->nullable()->index();
            $table->integer('AptStatus')->nullable()->index();
            $table->string('Pattern')->nullable();
            $table->integer('Confirmed')->nullable();
            $table->integer('TimeLocked')->nullable();
            $table->integer('Op')->nullable();
            $table->text('Note')->nullable();
            $table->bigInteger('ProvNum')->nullable()->index();
            $table->bigInteger('ProvHyg')->nullable();
            $table->dateTime('AptDateTime')->nullable()->index();
            $table->bigInteger('NextAptNum')->nullable();
            $table->integer('UnschedStatus')->nullable();
            $table->integer('IsNewPatient')->nullable();
            $table->text('ProcDescript')->nullable();
            $table->integer('Assistant')->nullable();
            $table->integer('ClinicNum')->nullable()->index();
            $table->integer('IsHygiene')->nullable();
            $table->string('DateTStamp')->nullable()->index();
            $table->dateTime('DateTimeArrived')->nullable();
            $table->dateTime('DateTimeSeated')->nullable();
            $table->dateTime('DateTimeDismissed')->nullable();
            $table->bigInteger('InsPlan1')->nullable();
            $table->bigInteger('InsPlan2')->nullable();
            $table->dateTime('DateTimeAskedToArrive')->nullable();
            $table->text('ProcsColored')->nullable();
            $table->integer('ColorOverride')->nullable();
            $table->integer('AppointmentTypeNum')->nullable();
            $table->bigInteger('SecUserNumEntry')->nullable();
            $table->dateTime('SecDateTEntry')->nullable();
            $table->integer('Priority')->nullable();
            $table->string('ProvBarText')->nullable();
            $table->string('PatternSecondary')->nullable();
            $table->string('SecurityHash')->nullable();
            $table->integer('ItemOrderPlanned')->nullable();
            $table->integer('IsMirrored')->nullable();
            $table->timestamps();

            $table->unique(['office_id', 'HistApptNum'], 'od_histappointments_office_histapptnum_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_histappointments');
    }
};
