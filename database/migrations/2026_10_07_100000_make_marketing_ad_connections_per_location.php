<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An ad platform is connected per reporting location, not once for the organization:
 * each office (or clinic) signs in to its own Google Ads account. The connection carries
 * the location; accounts discovered under it inherit that location unless re-mapped.
 *
 * A connection left without a location (rows from before this change) still works and is
 * listed as "not assigned" on the Integrations page until someone assigns it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('marketing_ad_connections')) {
            return;
        }

        Schema::table('marketing_ad_connections', function (Blueprint $table) {
            if (! Schema::hasColumn('marketing_ad_connections', 'office_id')) {
                $table->unsignedBigInteger('office_id')->nullable()->after('provider');
            }
            if (! Schema::hasColumn('marketing_ad_connections', 'clinic_num')) {
                $table->unsignedInteger('clinic_num')->nullable()->after('office_id');
            }
        });

        Schema::table('marketing_ad_connections', function (Blueprint $table) {
            $table->dropUnique('marketing_ad_connections_provider_unique');
            $table->index(['provider', 'office_id', 'clinic_num'], 'marketing_ad_connections_location_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('marketing_ad_connections')) {
            return;
        }

        Schema::table('marketing_ad_connections', function (Blueprint $table) {
            $table->dropIndex('marketing_ad_connections_location_index');
            $table->dropColumn(['office_id', 'clinic_num']);
            $table->unique('provider');
        });
    }
};
