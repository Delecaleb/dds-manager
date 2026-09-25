<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * run_token: owner of the current run (atomic lease, see SyncLease).
     * cycle_started_at: when the current initial full pass began, so a pass
     * split across several time-budgeted jobs still gets a correct watermark.
     */
    public function up(): void
    {
        Schema::table('sync_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('sync_logs', 'run_token')) {
                $table->string('run_token', 36)->nullable()->after('status');
            }

            if (! Schema::hasColumn('sync_logs', 'cycle_started_at')) {
                $table->timestamp('cycle_started_at')->nullable()->after('last_synced_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sync_logs', function (Blueprint $table) {
            $table->dropColumn(['run_token', 'cycle_started_at']);
        });
    }
};
