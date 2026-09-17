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
        $hasIndex = function (string $table, string $indexName): bool {
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
                return false;
            }
            $res = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

            return ! empty($res);
        };

        if (Schema::hasTable('od_procedure_logs')) {
            Schema::table('od_procedure_logs', function (Blueprint $table) use ($hasIndex) {
                if (! $hasIndex('od_procedure_logs', 'idx_proc_logs_ops_aptnum')) {
                    $table->index(['office_id', 'AptNum'], 'idx_proc_logs_ops_aptnum');
                }
                if (! $hasIndex('od_procedure_logs', 'idx_proc_logs_ops_datetp')) {
                    $table->index(['office_id', 'DateTP', 'ProcStatus'], 'idx_proc_logs_ops_datetp');
                }
                if (! $hasIndex('od_procedure_logs', 'idx_proc_logs_ops_prov_status_date')) {
                    $table->index(['office_id', 'ProvNum', 'ProcStatus', 'ProcDate'], 'idx_proc_logs_ops_prov_status_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('od_procedure_logs')) {
            Schema::table('od_procedure_logs', function (Blueprint $table) {
                $table->dropIndex('idx_proc_logs_ops_aptnum');
                $table->dropIndex('idx_proc_logs_ops_datetp');
                $table->dropIndex('idx_proc_logs_ops_prov_status_date');
            });
        }
    }
};
