<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - od_providers.FName: the provider sync pulls `SELECT *`, but persistBatch drops columns
 *   missing locally, so provider first names ("Haddow, Mason") were never stored.
 *   The provider sync cursors are rewound so the next run re-reads every provider
 *   (a few hundred rows per office) and backfills FName.
 * - od_treatment_plan_attachments(office_id, ProcNum): Tx Miner breakdown looks up the
 *   treatment plan a procedure is attached to.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('od_providers') && ! Schema::hasColumn('od_providers', 'FName')) {
            Schema::table('od_providers', function (Blueprint $table) {
                $table->string('FName')->nullable()->after('LName');
            });
        }

        if (Schema::hasTable('sync_logs')) {
            DB::table('sync_logs')
                ->where('module', 'like', 'office\_%:provider')
                ->update(['last_synced_at' => null, 'last_primary_key' => 0, 'cycle_started_at' => null]);
        }

        if (Schema::hasTable('od_treatment_plan_attachments') && ! $this->hasIndex('od_treatment_plan_attachments', 'idx_tpa_office_procnum')) {
            Schema::table('od_treatment_plan_attachments', function (Blueprint $table) {
                $table->index(['office_id', 'ProcNum'], 'idx_tpa_office_procnum');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('od_treatment_plan_attachments') && $this->hasIndex('od_treatment_plan_attachments', 'idx_tpa_office_procnum')) {
            Schema::table('od_treatment_plan_attachments', function (Blueprint $table) {
                $table->dropIndex('idx_tpa_office_procnum');
            });
        }

        if (Schema::hasTable('od_providers') && Schema::hasColumn('od_providers', 'FName')) {
            Schema::table('od_providers', function (Blueprint $table) {
                $table->dropColumn('FName');
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))->contains(fn (array $index) => $index['name'] === $indexName);
    }
};
