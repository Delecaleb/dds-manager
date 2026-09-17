<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasIndex = function (string $table, string $indexName): bool {
            if (DB::getDriverName() === 'sqlite') {
                return false;
            }
            $res = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

            return ! empty($res);
        };

        if (Schema::hasTable('od_claim_procs')) {
            Schema::table('od_claim_procs', function (Blueprint $table) use ($hasIndex) {
                if (! $hasIndex('od_claim_procs', 'idx_claim_procs_ops_pat_plan')) {
                    $table->index(['office_id', 'PatNum', 'PlanNum', 'ClaimProcNum'], 'idx_claim_procs_ops_pat_plan');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('od_claim_procs')) {
            Schema::table('od_claim_procs', function (Blueprint $table) {
                $table->dropIndex('idx_claim_procs_ops_pat_plan');
            });
        }
    }
};
