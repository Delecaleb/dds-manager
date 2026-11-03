<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The Growth Engine is now granted per page ("marketing:leads", …). Users who already had
 * the whole module keep every page, so nobody loses access on deploy. Data only, no DDL.
 *
 * The page list is a snapshot of ModuleManager at this date; pages added later are granted
 * through the admin form, not here.
 */
return new class extends Migration
{
    private const PAGES = [
        'overview', 'websites', 'journeys', 'funnel', 'channels', 'campaigns',
        'leads', 'alerts', 'tracking', 'integrations', 'front-desk', 'site-builder',
    ];

    public function up(): void
    {
        $now = now();

        DB::table('user_modules')->where('module_key', 'marketing')->orderBy('id')
            ->each(function (object $row) use ($now) {
                DB::table('user_modules')->insertOrIgnore(array_map(fn (string $page) => [
                    'user_id' => $row->user_id,
                    'module_key' => 'marketing:'.$page,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], self::PAGES));
            });
    }

    public function down(): void
    {
        DB::table('user_modules')->where('module_key', 'like', 'marketing:%')->delete();
    }
};
