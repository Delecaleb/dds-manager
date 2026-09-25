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
        if (! Schema::hasTable('office_goals')) {
            Schema::create('office_goals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id')->index();
                $table->unsignedBigInteger('clinic_num')->nullable()->index();
                $table->string('year_month', 7)->index(); // 'YYYY-MM', e.g. '2026-09'
                $table->string('goal_type', 20)->default('monthly'); // 'monthly' or 'daily'
                $table->decimal('gross_production', 14, 2)->default(0);
                $table->decimal('net_production', 14, 2)->default(0);
                $table->decimal('collection', 14, 2)->default(0);
                $table->integer('pts_visits')->default(0);
                $table->integer('npt_visits')->default(0);
                $table->integer('ini_bonding')->default(0);
                $table->integer('hyg_visits')->default(0);
                $table->timestamps();

                $table->unique(['office_id', 'clinic_num', 'year_month', 'goal_type'], 'office_goals_unique_idx');
            });
        }

        if (! Schema::hasTable('specialty_goals')) {
            Schema::create('specialty_goals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id')->index();
                $table->unsignedBigInteger('clinic_num')->nullable()->index();
                $table->string('year_month', 7)->index();
                $table->string('goal_type', 20)->default('monthly');
                $table->decimal('doctor', 14, 2)->default(0);
                $table->decimal('hygiene', 14, 2)->default(0);
                $table->decimal('oral_surgery', 14, 2)->default(0);
                $table->decimal('clear_aligners', 14, 2)->default(0);
                $table->decimal('perio', 14, 2)->default(0);
                $table->decimal('pedo', 14, 2)->default(0);
                $table->decimal('endo', 14, 2)->default(0);
                $table->decimal('ortho', 14, 2)->default(0);
                $table->decimal('prostho', 14, 2)->default(0);
                $table->timestamps();

                $table->unique(['office_id', 'clinic_num', 'year_month', 'goal_type'], 'specialty_goals_unique_idx');
            });
        }

        if (! Schema::hasTable('provider_goals')) {
            Schema::create('provider_goals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id')->index();
                $table->unsignedBigInteger('clinic_num')->nullable()->index();
                $table->unsignedBigInteger('prov_num')->index();
                $table->string('provider_name')->nullable();
                $table->string('provider_type', 50)->nullable();
                $table->string('year_month', 7)->index();
                $table->string('goal_type', 20)->default('monthly');
                $table->boolean('recurring')->default(false);
                $table->decimal('production_goal', 14, 2)->default(0);
                $table->timestamps();

                $table->unique(['office_id', 'clinic_num', 'prov_num', 'year_month', 'goal_type'], 'provider_goals_unique_idx');
            });
        }

        if (! Schema::hasTable('kpi_configurations')) {
            Schema::create('kpi_configurations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id')->nullable()->index();
                $table->unsignedBigInteger('clinic_num')->nullable()->index();
                $table->string('kpi_key', 100)->index();
                $table->string('category', 50)->default('main');
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->decimal('target_goal', 14, 2)->default(0);
                $table->timestamps();

                $table->unique(['office_id', 'clinic_num', 'kpi_key', 'category'], 'kpi_config_unique_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_configurations');
        Schema::dropIfExists('provider_goals');
        Schema::dropIfExists('specialty_goals');
        Schema::dropIfExists('office_goals');
    }
};
