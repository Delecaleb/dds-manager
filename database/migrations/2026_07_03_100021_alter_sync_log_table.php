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
        Schema::table('sync_logs', function (Blueprint $table) {

            // Module should be unique
            $table->string('module')->unique()->change();

            // Last synced timestamp
            $table->timestamp('last_synced_at')->nullable()->change();

            // Resume checkpoint
            $table->unsignedBigInteger('last_primary_key')->nullable()->after('module');

            // Progress tracking
            $table->unsignedBigInteger('total_processed')->default(0)->after('last_synced_at');

            // Retry tracking
            $table->unsignedTinyInteger('retry_count')->default(0)->after('total_processed');

            // idle | running | completed | failed
            $table->string('status')->default('idle')->after('retry_count');

            // Store last exception
            $table->longText('last_error')->nullable()->after('status');

            // Job timestamps
            $table->timestamp('started_at')->nullable()->after('last_error');

            $table->timestamp('finished_at')->nullable()->after('started_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sync_logs', function (Blueprint $table) {
            $table->dropColumn([
                'last_primary_key',
                'total_processed',
                'retry_count',
                'status',
                'last_error',
                'started_at',
                'finished_at',
            ]);

            $table->string('module')->change();

            $table->timestamp('last_synced_at')->change();
        });
    }
};
