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
        if (! Schema::hasTable('sync_log_prune')) {
            Schema::create('sync_log_prune', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('office_id')->default(1)->index();
                $table->string('table_name', 100)->index();
                $table->string('mode', 50)->default('range');
                $table->string('range', 100)->nullable();
                $table->unsignedInteger('local_count')->default(0);
                $table->unsignedInteger('remote_count')->nullable();
                $table->unsignedInteger('orphan_count')->default(0);
                $table->string('status', 30)->default('running');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['office_id', 'table_name', 'created_at'], 'sync_log_prune_office_tbl_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_log_prune');
    }
};
