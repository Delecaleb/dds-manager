<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One tracked website can serve several locations (one script on a shared site).
 *
 *  - marketing_site_locations: the locations a site serves. Each carries the value the
 *    site's signup form sends to name it (`code`, null = derived from the location name)
 *    and the URL paths that belong to it (`path_prefixes`, e.g. ["/grand-rapids"]).
 *  - marketing_visitors and marketing_sessions carry the location they are credited to,
 *    and how it was decided (`location_source`: site | path | patient | form). NULL means
 *    not known yet: the visit counts once in the all-locations view and in no location.
 *  - marketing_sites loses office_id / clinic_num: the site's existing location moves to
 *    marketing_site_locations, and its existing traffic is credited to it ('site').
 */
return new class extends Migration
{
    /**
     * Every step checks whether it is already done, so a run that stopped part-way (MySQL
     * cannot roll back schema changes) is finished by simply running migrate again. Nothing
     * is dropped except marketing_sites.office_id / clinic_num, and only once every site's
     * location is confirmed copied into marketing_site_locations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('marketing_site_locations')) {
            Schema::create('marketing_site_locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('marketing_sites')->cascadeOnDelete();
                $table->unsignedBigInteger('office_id');
                $table->unsignedInteger('clinic_num')->nullable(); // null = the office as a whole
                $table->string('code', 64)->nullable();            // what the signup form sends; null = from the name
                $table->json('path_prefixes')->nullable();         // URL paths that belong to this location
                $table->timestamps();

                $table->index('site_id');
                $table->index(['office_id', 'clinic_num']);
            });
        }

        // Appended (no ->after()), so MySQL 8 adds them instantly instead of rebuilding the table.
        foreach (['marketing_visitors', 'marketing_sessions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['office_id', 'clinic_num', 'location_source'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        continue;
                    }

                    match ($column) {
                        'office_id' => $table->unsignedBigInteger('office_id')->nullable(),
                        'clinic_num' => $table->unsignedInteger('clinic_num')->nullable(),
                        'location_source' => $table->string('location_source', 16)->nullable(),
                    };
                }
            });
        }

        $this->addIndex('marketing_sessions', ['office_id', 'started_at'], 'marketing_sessions_location_started_index');
        $this->addIndex('marketing_visitors', ['office_id', 'identified_at'], 'marketing_visitors_location_identified_index');
        $this->addIndex('marketing_visitors', ['office_id', 'last_seen_at'], 'marketing_visitors_location_seen_index');

        if (! Schema::hasColumn('marketing_sites', 'office_id')) {
            return; // already moved on an earlier run
        }

        $now = now();
        $sites = DB::table('marketing_sites')->whereNotNull('office_id')->get(['id', 'office_id', 'clinic_num']);

        foreach ($sites as $site) {
            $copied = DB::table('marketing_site_locations')->where('site_id', $site->id)
                ->where('office_id', $site->office_id)->exists();

            if (! $copied) {
                DB::table('marketing_site_locations')->insert([
                    'site_id' => $site->id,
                    'office_id' => $site->office_id,
                    'clinic_num' => $site->clinic_num,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Only rows not stamped yet, so a re-run never overwrites a credit.
            foreach (['marketing_visitors', 'marketing_sessions'] as $tableName) {
                DB::table($tableName)->where('site_id', $site->id)->whereNull('location_source')->update([
                    'office_id' => $site->office_id,
                    'clinic_num' => $site->clinic_num,
                    'location_source' => 'site',
                ]);
            }
        }

        $missing = $sites->filter(fn ($site) => ! DB::table('marketing_site_locations')
            ->where('site_id', $site->id)->where('office_id', $site->office_id)->exists());

        if ($missing->isNotEmpty()) {
            throw new RuntimeException('Not dropping marketing_sites.office_id: the location of site(s) '
                .$missing->pluck('id')->implode(', ').' was not copied to marketing_site_locations.');
        }

        Schema::table('marketing_sites', function (Blueprint $table) {
            $table->dropColumn(['office_id', 'clinic_num']);
        });
    }

    /** @param list<string> $columns */
    private function addIndex(string $tableName, array $columns, string $name): void
    {
        if (! Schema::hasIndex($tableName, $name)) {
            Schema::table($tableName, fn (Blueprint $table) => $table->index($columns, $name));
        }
    }

    public function down(): void
    {
        Schema::table('marketing_sites', function (Blueprint $table) {
            $table->unsignedBigInteger('office_id')->nullable()->after('domain');
            $table->unsignedInteger('clinic_num')->nullable()->after('office_id');
        });

        // A site goes back to its first location; extra locations are lost.
        foreach (DB::table('marketing_site_locations')->orderBy('id')->get() as $location) {
            DB::table('marketing_sites')->where('id', $location->site_id)->whereNull('office_id')
                ->update(['office_id' => $location->office_id, 'clinic_num' => $location->clinic_num]);
        }

        Schema::table('marketing_visitors', function (Blueprint $table) {
            $table->dropIndex('marketing_visitors_location_identified_index');
            $table->dropIndex('marketing_visitors_location_seen_index');
        });
        Schema::table('marketing_sessions', function (Blueprint $table) {
            $table->dropIndex('marketing_sessions_location_started_index');
        });

        foreach (['marketing_visitors', 'marketing_sessions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['office_id', 'clinic_num', 'location_source']);
            });
        }

        Schema::dropIfExists('marketing_site_locations');
    }
};
