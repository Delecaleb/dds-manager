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
        if (Schema::hasTable('od_patients')) {
            Schema::table('od_patients', function (Blueprint $table) {
                if (! Schema::hasColumn('od_patients', 'DateTStamp')) {
                    $table->string('DateTStamp')->nullable()->after('GradeLevel');
                }
                if (! Schema::hasColumn('od_patients', 'SecDateEntry')) {
                    $table->date('SecDateEntry')->nullable()->after('DateTStamp');
                }
                if (! Schema::hasColumn('od_patients', 'SecUserNumEntry')) {
                    $table->integer('SecUserNumEntry')->nullable()->after('SecDateEntry');
                }
                if (! Schema::hasColumn('od_patients', 'DateFirstVisit')) {
                    $table->date('DateFirstVisit')->nullable()->after('SecUserNumEntry');
                }
                if (! Schema::hasColumn('od_patients', 'ClinicNum')) {
                    $table->integer('ClinicNum')->nullable()->after('DateFirstVisit');
                }
                if (! Schema::hasColumn('od_patients', 'HasIns')) {
                    $table->string('HasIns')->nullable()->after('ClinicNum');
                }
                if (! Schema::hasColumn('od_patients', 'Urgency')) {
                    $table->integer('Urgency')->nullable()->after('HasIns');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('od_patients')) {
            Schema::table('od_patients', function (Blueprint $table) {
                $columns = ['DateTStamp', 'SecDateEntry', 'SecUserNumEntry', 'DateFirstVisit', 'ClinicNum', 'HasIns', 'Urgency'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('od_patients', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
