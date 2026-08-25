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
        // Schema::create('sync_requests', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('office_id')->default(1);
        //     $table->string('module', 50);
        //     $table->date('start_date')->nullable();
        //     $table->date('end_date')->nullable();
        //     $table->boolean('prune_deleted')->default(false);
        //     $table->string('status', 50)->default('pending'); // pending, running, completed, failed, cancelled
        //     $table->integer('total_processed')->default(0);
        //     $table->text('error_message')->nullable();
        //     $table->timestamp('started_at')->nullable();
        //     $table->timestamp('completed_at')->nullable();
        //     $table->unsignedBigInteger('created_by')->nullable();
        //     $table->timestamps();

        //     $table->index(['status', 'created_at']);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('sync_requests');
    }
};
