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
        Schema::table('od_patients', function (Blueprint $table) {
            if (! Schema::hasColumn('od_patients', 'SecDateEntry')) {
                $table->date('SecDateEntry')->nullable()->after('Birthdate');
            }
            if (! Schema::hasColumn('od_patients', 'DateFirstVisit')) {
                $table->date('DateFirstVisit')->nullable()->after('Birthdate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('od_patients', function (Blueprint $table) {
            if (Schema::hasColumn('od_patients', 'SecDateEntry')) {
                $table->dropColumn('SecDateEntry');
            }
            if (Schema::hasColumn('od_patients', 'DateFirstVisit')) {
                $table->dropColumn('DateFirstVisit');
            }
        });
    }
};
