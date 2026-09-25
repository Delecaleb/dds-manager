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

        if (Schema::hasTable('od_recalls')) {
            Schema::table('od_recalls', function (Blueprint $table) use ($hasIndex) {
                if (! $hasIndex('od_recalls', 'od_recalls_office_datedue_index')) {
                    $table->index(['office_id', 'DateDue'], 'od_recalls_office_datedue_index');
                }
                if (! $hasIndex('od_recalls', 'od_recalls_office_patnum_index')) {
                    $table->index(['office_id', 'PatNum'], 'od_recalls_office_patnum_index');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('od_recalls')) {
            Schema::table('od_recalls', function (Blueprint $table) {
                $table->dropIndex('od_recalls_office_datedue_index');
                $table->dropIndex('od_recalls_office_patnum_index');
            });
        }
    }
};
