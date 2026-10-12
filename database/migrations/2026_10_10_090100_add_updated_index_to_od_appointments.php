<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Serves the Developer API's `updated_since` filter: one location's appointments changed
 * after a point in time. Date-range reads are already served by idx_od_appts_ops_date
 * (office_id, AptDateTime, ClinicNum). InnoDB adds a secondary index online, without
 * blocking the sync's writes.
 */
return new class extends Migration
{
    private const INDEX = 'idx_od_appts_office_tstamp';

    public function up(): void
    {
        if (! Schema::hasTable('od_appointments') || $this->hasIndex()) {
            return;
        }

        Schema::table('od_appointments', function (Blueprint $table) {
            $table->index(['office_id', 'DateTStamp'], self::INDEX);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('od_appointments') && $this->hasIndex()) {
            Schema::table('od_appointments', function (Blueprint $table) {
                $table->dropIndex(self::INDEX);
            });
        }
    }

    private function hasIndex(): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return DB::select("SELECT 1 FROM sqlite_master WHERE type = 'index' AND name = ?", [self::INDEX]) !== [];
        }

        return DB::select('SHOW INDEX FROM `od_appointments` WHERE Key_name = ?', [self::INDEX]) !== [];
    }
};
