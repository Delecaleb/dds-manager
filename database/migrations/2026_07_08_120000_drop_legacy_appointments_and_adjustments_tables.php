<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes two stray, empty legacy tables (`appointments`, `adjustments`) that
 * predate the OpenDental sync layer. The application reads exclusively from
 * the synced `od_appointments` (72k+ rows) and `od_adjustments` (13k+ rows)
 * tables via the Od* Eloquent models — nothing in the codebase references the
 * bare `appointments`/`adjustments` tables, and both hold zero rows, so this
 * is a safe cleanup with no data loss.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('adjustments');
    }

    /**
     * These tables were never created by a migration and their original
     * schema is unknown/unused, so the reversal simply recreates empty
     * placeholders (id + timestamps) to keep the migration technically
     * reversible without inventing a schema the app never relied on.
     */
    public function down(): void
    {
        foreach (['appointments', 'adjustments'] as $name) {
            if (! Schema::hasTable($name)) {
                Schema::create($name, function (Blueprint $table) {
                    $table->id();
                    $table->timestamps();
                });
            }
        }
    }
};
