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
        if (! Schema::hasTable('eod_configurations')) {
            Schema::create('eod_configurations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id')->nullable()->index();
                $table->unsignedBigInteger('clinic_num')->nullable()->index();
                $table->string('metric_key', 100)->index();
                $table->string('subtab', 50)->default('basics')->index();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_locked')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['office_id', 'clinic_num', 'subtab', 'metric_key'], 'eod_config_unique_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eod_configurations');
    }
};
