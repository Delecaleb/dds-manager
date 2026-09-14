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
        if (Schema::hasTable('kpi_configurations')) {
            Schema::table('kpi_configurations', function (Blueprint $table) {
                if (! Schema::hasColumn('kpi_configurations', 'transaction_type')) {
                    $table->string('transaction_type', 100)->nullable()->after('category');
                }
                if (! Schema::hasColumn('kpi_configurations', 'kpi_type')) {
                    $table->string('kpi_type', 100)->nullable()->after('transaction_type');
                }
                if (! Schema::hasColumn('kpi_configurations', 'line_of_business')) {
                    $table->string('line_of_business', 100)->nullable()->after('kpi_type');
                }
                if (! Schema::hasColumn('kpi_configurations', 'display_type')) {
                    $table->string('display_type', 100)->nullable()->after('line_of_business');
                }
                if (! Schema::hasColumn('kpi_configurations', 'metadata')) {
                    $table->json('metadata')->nullable()->after('target_goal');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kpi_configurations')) {
            Schema::table('kpi_configurations', function (Blueprint $table) {
                $columns = ['transaction_type', 'kpi_type', 'line_of_business', 'display_type', 'metadata'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('kpi_configurations', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
