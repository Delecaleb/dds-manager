<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasIndex = function (string $table, string $indexName): bool {
            if (DB::getDriverName() === 'sqlite') {
                return false;
            }
            $res = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

            return ! empty($res);
        };

        if (Schema::hasTable('od_procedure_logs')) {
            Schema::table('od_procedure_logs', function (Blueprint $table) use ($hasIndex) {
                // (office_id,ProcStatus,ProcDate,ClinicNum) is intentionally NOT created here:
                // 2026_09_17_181719 adds the same columns plus PatNum, which serves every query
                // this one would. See 2026_09_17_190000_drop_redundant_operations_indexes.
                if (! $hasIndex('od_procedure_logs', 'idx_proc_logs_ops_pat_status_date')) {
                    $table->index(['office_id', 'PatNum', 'ProcStatus', 'ProcDate'], 'idx_proc_logs_ops_pat_status_date');
                }
            });
        }

        if (Schema::hasTable('od_appointments')) {
            Schema::table('od_appointments', function (Blueprint $table) use ($hasIndex) {
                // (office_id,AptStatus,AptDateTime,ClinicNum) is intentionally NOT created here:
                // 2026_09_17_181719 adds the same columns plus PatNum and covers it.
                if (! $hasIndex('od_appointments', 'idx_od_appts_ops_date')) {
                    $table->index(['office_id', 'AptDateTime', 'ClinicNum'], 'idx_od_appts_ops_date');
                }
            });
        }

        if (Schema::hasTable('od_claim_procs')) {
            Schema::table('od_claim_procs', function (Blueprint $table) use ($hasIndex) {
                if (! $hasIndex('od_claim_procs', 'idx_claim_procs_ops_datecp')) {
                    $table->index(['office_id', 'DateCP', 'Status', 'ClinicNum'], 'idx_claim_procs_ops_datecp');
                }
                if (! $hasIndex('od_claim_procs', 'idx_claim_procs_ops_procdate')) {
                    $table->index(['office_id', 'ProcDate', 'ClinicNum'], 'idx_claim_procs_ops_procdate');
                }
            });
        }

        if (Schema::hasTable('od_pay_splits')) {
            Schema::table('od_pay_splits', function (Blueprint $table) use ($hasIndex) {
                if (! $hasIndex('od_pay_splits', 'idx_pay_splits_ops_datepay')) {
                    $table->index(['office_id', 'DatePay', 'ClinicNum'], 'idx_pay_splits_ops_datepay');
                }
            });
        }

        if (Schema::hasTable('od_adjustments')) {
            Schema::table('od_adjustments', function (Blueprint $table) use ($hasIndex) {
                if (! $hasIndex('od_adjustments', 'idx_adjustments_ops_adjdate')) {
                    $table->index(['office_id', 'AdjDate', 'ClinicNum'], 'idx_adjustments_ops_adjdate');
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
                $table->dropIndex('idx_proc_logs_ops_pat_status_date');
            });
        }

        if (Schema::hasTable('od_appointments')) {
            Schema::table('od_appointments', function (Blueprint $table) {
                $table->dropIndex('idx_od_appts_ops_date');
            });
        }

        if (Schema::hasTable('od_claim_procs')) {
            Schema::table('od_claim_procs', function (Blueprint $table) {
                $table->dropIndex('idx_claim_procs_ops_datecp');
                $table->dropIndex('idx_claim_procs_ops_procdate');
            });
        }

        if (Schema::hasTable('od_pay_splits')) {
            Schema::table('od_pay_splits', function (Blueprint $table) {
                $table->dropIndex('idx_pay_splits_ops_datepay');
            });
        }

        if (Schema::hasTable('od_adjustments')) {
            Schema::table('od_adjustments', function (Blueprint $table) {
                $table->dropIndex('idx_adjustments_ops_adjdate');
            });
        }
    }
};
