<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * od_procedures.ProcCat: VARCHAR(255) → BIGINT, the type of od_definitions.DefNum it joins.
 *
 * `pc.ProcCat = d.DefNum` compared text with a BIGINT, so the definitions index could not be
 * used (Financials Top Services: 4.4 s). Checked 2026-10-27: 6,596 rows, all plain integers.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (collect(Schema::getColumns('od_procedures'))->firstWhere('name', 'ProcCat')['type_name'] === 'bigint') {
            return;
        }

        Schema::table('od_procedures', function (Blueprint $table) {
            $table->bigInteger('ProcCat')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('od_procedures', function (Blueprint $table) {
            $table->string('ProcCat', 255)->nullable()->change();
        });
    }
};
