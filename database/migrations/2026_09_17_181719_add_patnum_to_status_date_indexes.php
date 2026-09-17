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
        $isMysql = in_array(\Illuminate\Support\Facades\DB::getDriverName(), ['mysql', 'mariadb'], true);

        if ($isMysql) {
            \Illuminate\Support\Facades\DB::statement("SET SESSION sql_mode = ''");
        }

        $hasIndex = function (string $table, string $indexName) use ($isMysql): bool {
            if (! $isMysql) {
                return false;
            }
            $res = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

            return ! empty($res);
        };

        if (Schema::hasTable('od_procedure_logs')) {
            if ($isMysql) {
                \Illuminate\Support\Facades\DB::statement('
                    ALTER TABLE `od_procedure_logs`
                    MODIFY `PatNum` BIGINT NULL,
                    MODIFY `ClinicNum` BIGINT NULL,
                    MODIFY `AptNum` BIGINT NULL,
                    MODIFY `ProvNum` BIGINT NULL,
                    MODIFY `ProcStatus` VARCHAR(32) NULL
                ');
            }

            Schema::table('od_procedure_logs', function (Blueprint $table) use ($hasIndex) {
                if (! $hasIndex('od_procedure_logs', 'idx_proc_logs_ops_status_date_pat')) {
                    $table->index(['office_id', 'ProcStatus', 'ProcDate', 'ClinicNum', 'PatNum'], 'idx_proc_logs_ops_status_date_pat');
                }
            });
        }

        if (Schema::hasTable('od_appointments')) {
            if ($isMysql) {
                \Illuminate\Support\Facades\DB::statement('
                    ALTER TABLE `od_appointments`
                    MODIFY `PatNum` BIGINT NULL,
                    MODIFY `ClinicNum` BIGINT NULL,
                    MODIFY `AptNum` BIGINT NULL,
                    MODIFY `ProvNum` BIGINT NULL,
                    MODIFY `AptStatus` VARCHAR(32) NULL
                ');
            }

            Schema::table('od_appointments', function (Blueprint $table) use ($hasIndex) {
                if (! $hasIndex('od_appointments', 'idx_od_appts_ops_status_date_pat')) {
                    $table->index(['office_id', 'AptStatus', 'AptDateTime', 'ClinicNum', 'PatNum'], 'idx_od_appts_ops_status_date_pat');
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
                $table->dropIndex('idx_proc_logs_ops_status_date_pat');
            });
        }

        if (Schema::hasTable('od_appointments')) {
            Schema::table('od_appointments', function (Blueprint $table) {
                $table->dropIndex('idx_od_appts_ops_status_date_pat');
            });
        }
    }
};
