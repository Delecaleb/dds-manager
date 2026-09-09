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
        Schema::create('od_daily_schedule_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('office_id')->default(1)->index();
            $table->unsignedBigInteger('clinic_num')->default(0)->index();
            $table->date('snapshot_date')->index();
            $table->decimal('sched_production', 12, 2)->default(0.00);
            $table->unsignedInteger('sched_pts_visit')->default(0);
            $table->unsignedInteger('sched_new_pts_visit')->default(0);
            $table->decimal('open_appt_hours', 8, 2)->default(0.00);
            $table->decimal('unscheduled_tx', 12, 2)->default(0.00);
            $table->boolean('is_locked')->default(false)->index();
            $table->timestamp('snapshot_taken_at')->nullable();
            $table->timestamps();

            $table->unique(['office_id', 'clinic_num', 'snapshot_date'], 'od_daily_sched_snap_unique');
        });

        Schema::create('od_appointment_schedule_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('office_id')->default(1)->index();
            $table->unsignedBigInteger('clinic_num')->default(0)->index();
            $table->date('snapshot_date')->index();
            $table->unsignedBigInteger('apt_num')->index();
            $table->unsignedBigInteger('pat_num')->index();
            $table->unsignedBigInteger('prov_num')->default(0)->index();
            $table->dateTime('apt_date_time')->nullable();
            $table->tinyInteger('apt_status')->default(1);
            $table->string('pattern', 255)->nullable();
            $table->boolean('is_new_patient')->default(false);
            $table->text('proc_descript')->nullable();
            $table->decimal('sched_production', 12, 2)->default(0.00);
            $table->decimal('unscheduled_tx', 12, 2)->default(0.00);
            $table->boolean('is_locked')->default(false)->index();
            $table->timestamp('snapshot_taken_at')->nullable();
            $table->timestamps();

            $table->unique(['office_id', 'apt_num', 'snapshot_date'], 'od_appt_sched_snap_unique');
            $table->index(['office_id', 'snapshot_date'], 'od_appt_sched_snap_office_date');
            $table->index(['office_id', 'clinic_num', 'snapshot_date'], 'od_appt_sched_snap_office_clinic_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('od_appointment_schedule_snapshots');
        Schema::dropIfExists('od_daily_schedule_snapshots');
    }
};
