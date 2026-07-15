<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Converts od_appointments.AptDateTime from varchar to a real DATETIME.
 *
 * The sync stored OpenDental's raw ISO-8601 strings verbatim (with a 'T'
 * separator, e.g. "2028-06-08T13:00:00"). As a varchar, every range filter
 * compared lexically, so day bounds built with a space separator matched
 * nothing — the calendar and every AptDateTime range query came back empty.
 *
 * Making it a true DATETIME fixes range semantics for good and lets us index
 * the column for fast date-range scans. The sync now normalizes AptDateTime
 * on write (AppointmentSyncService::transformRow), so future rows stay clean.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite is dynamically typed and does not support STR_TO_DATE or MODIFY/MODIFY COLUMN.
            // Simply make sure database has index.
            try {
                Schema::table('od_appointments', function (Blueprint $table) {
                    $table->index('AptDateTime', 'od_appointments_aptdatetime_index');
                });
            } catch (Exception $e) {
                // Ignore if index already exists
            }

            return;
        }

        // 1. Normalize the ISO 'T' separator to MySQL's space format.
        DB::statement("UPDATE od_appointments SET AptDateTime = REPLACE(AptDateTime, 'T', ' ') WHERE AptDateTime LIKE '%T%'");

        // 2. Null out anything that can't be represented as a DATETIME —
        //    blanks, unparseable text, and OpenDental sentinel dates (e.g.
        //    0001-01-01) below MySQL's 1000-01-01 minimum. Current data is
        //    clean; this guarantees the ALTER cannot fail on any environment.
        DB::statement("
            UPDATE od_appointments
            SET AptDateTime = NULL
            WHERE AptDateTime IS NOT NULL
              AND (
                    AptDateTime = ''
                 OR STR_TO_DATE(AptDateTime, '%Y-%m-%d %H:%i:%s') IS NULL
                 OR AptDateTime < '1000-01-01 00:00:00'
                 OR AptDateTime > '9999-12-31 23:59:59'
              )
        ");

        // 3. Convert the column type. All remaining values are valid
        //    'Y-m-d H:i:s' strings, which MySQL casts to DATETIME in place.
        DB::statement('ALTER TABLE od_appointments MODIFY AptDateTime DATETIME NULL');

        // 4. Index for fast range scans (calendar, KPIs, financials, ...).
        Schema::table('od_appointments', function (Blueprint $table) {
            $table->index('AptDateTime', 'od_appointments_aptdatetime_index');
        });
    }

    public function down(): void
    {
        Schema::table('od_appointments', function (Blueprint $table) {
            $table->dropIndex('od_appointments_aptdatetime_index');
        });

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Revert the type only. Values stay in normalized 'Y-m-d H:i:s' form
        // (the original 'T' separator is not restored — nothing relies on it).
        DB::statement('ALTER TABLE od_appointments MODIFY AptDateTime VARCHAR(255) NULL');
    }
};
