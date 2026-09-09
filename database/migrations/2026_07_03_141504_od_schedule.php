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
        Schema::create('od_schedules', function (Blueprint $table) {

            $table->id();
            $table->integer('ScheduleNum')->nullable();
            $table->date('SchedDate')->nullable();

            $table->string('StartTime')->nullable();

            $table->string('StopTime')->nullable();

            $table->integer('SchedType')->nullable();

            $table->integer('ProvNum')->nullable();

            $table->integer('BlockoutType')->nullable();

            $table->text('Note')->nullable();

            $table->integer('Status')->nullable();

            $table->integer('EmployeeNum')->nullable();

            $table->string('DateTStamp')->nullable();

            $table->integer('ClinicNum')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('od_pay_splits');
    }
};
