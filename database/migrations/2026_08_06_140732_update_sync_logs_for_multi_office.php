<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sync_logs')) {
            return;
        }

        if (! Schema::hasColumn('sync_logs', 'office_id')) {
            Schema::table('sync_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('office_id')->default(1)->index()->after('id');
            });
        }

        $logs = DB::table('sync_logs')->get();

        foreach ($logs as $log) {
            $module = $log->module;
            $officeId = 1;

            if (preg_match('/^office_(\d+):/', $module, $matches)) {
                $officeId = (int) $matches[1];
                DB::table('sync_logs')
                    ->where('id', $log->id)
                    ->update(['office_id' => $officeId]);
            } else {
                $newModule = "office_1:{$module}";
                DB::table('sync_logs')
                    ->where('id', $log->id)
                    ->update([
                        'module' => $newModule,
                        'office_id' => 1,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
