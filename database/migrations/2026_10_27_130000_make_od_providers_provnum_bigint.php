<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * od_providers.ProvNum: VARCHAR(255) → BIGINT, the type every other od_* table uses.
 *
 * Joins like `pl.ProvNum = pr.ProvNum` compared a BIGINT with text, so MySQL could not use
 * the (office_id, ProvNum) index and matched each row against every provider of the office
 * (Financials Score Cards: 12.8 s). Checked 2026-10-27: 539 rows, all plain integers.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (collect(Schema::getColumns('od_providers'))->firstWhere('name', 'ProvNum')['type_name'] === 'bigint') {
            return;
        }

        Schema::table('od_providers', function (Blueprint $table) {
            $table->bigInteger('ProvNum')->change();
        });
    }

    public function down(): void
    {
        Schema::table('od_providers', function (Blueprint $table) {
            $table->string('ProvNum', 255)->change();
        });
    }
};
