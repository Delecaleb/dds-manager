<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Google Ads stops being "connected per location".
 *
 *  - A connection is one Google login, identified by its email; it has no location.
 *  - An ad account exists once, whichever connection(s) can see it (unique provider +
 *    external_id). Duplicates created when two locations connected the same login are
 *    removed, keeping the oldest copy; their campaigns and stats go with them (they repeat
 *    the kept copy's, and re-sync within the history window).
 *  - An account is assigned to one or more locations (marketing_ad_account_locations) by an
 *    admin, and is not synced until it has at least one. Locations auto-inherited from the
 *    connection are discarded: they were guesses, wrong for agency logins that see other
 *    practices' accounts.
 *  - history_from caps how far back stats are pulled: 90 days before the account was
 *    first connected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_ad_connections', function (Blueprint $table) {
            $table->string('account_email')->nullable()->after('provider');
            $table->index(['provider', 'account_email'], 'marketing_ad_connections_provider_email_index');
        });

        if (Schema::hasIndex('marketing_ad_connections', 'marketing_ad_connections_location_index')) {
            Schema::table('marketing_ad_connections', function (Blueprint $table) {
                $table->dropIndex('marketing_ad_connections_location_index');
            });
        }
        Schema::table('marketing_ad_connections', function (Blueprint $table) {
            $table->dropColumn(['office_id', 'clinic_num']);
        });

        // Keep the oldest copy of each account; deleting the rest cascades to their campaigns/stats.
        $keep = DB::table('marketing_ad_accounts')->selectRaw('MIN(id) as id')->groupBy('external_id')->pluck('id')->all();
        DB::table('marketing_ad_accounts')->whereNotIn('id', $keep === [] ? [0] : $keep)->delete();

        Schema::table('marketing_ad_accounts', function (Blueprint $table) {
            $table->string('provider', 32)->default('google_ads')->after('connection_id');
            $table->date('history_from')->nullable()->after('status');
            // The FK on connection_id needs an index of its own once the composite unique goes.
            $table->index('connection_id', 'marketing_ad_accounts_connection_index');
        });

        Schema::table('marketing_ad_accounts', function (Blueprint $table) {
            $table->dropUnique(['connection_id', 'external_id']);
            $table->dropIndex(['office_id']);
            $table->unique(['provider', 'external_id'], 'marketing_ad_accounts_provider_external_unique');
        });

        Schema::table('marketing_ad_accounts', function (Blueprint $table) {
            $table->dropColumn(['office_id', 'clinic_num', 'is_enabled']);
        });

        foreach (DB::table('marketing_ad_accounts')->get(['id', 'created_at']) as $account) {
            $connected = $account->created_at !== null ? substr((string) $account->created_at, 0, 10) : now()->toDateString();
            DB::table('marketing_ad_accounts')->where('id', $account->id)
                ->update(['history_from' => date('Y-m-d', strtotime($connected.' -90 days'))]);
        }

        Schema::create('marketing_ad_account_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('marketing_ad_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('office_id');
            $table->unsignedInteger('clinic_num')->nullable(); // null = the office as a whole
            $table->timestamps();

            $table->index('account_id');
            $table->index(['office_id', 'clinic_num']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_ad_account_locations');

        Schema::table('marketing_ad_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('office_id')->nullable()->index();
            $table->unsignedInteger('clinic_num')->nullable();
            $table->boolean('is_enabled')->default(true);
        });

        Schema::table('marketing_ad_accounts', function (Blueprint $table) {
            $table->dropUnique('marketing_ad_accounts_provider_external_unique');
            $table->unique(['connection_id', 'external_id']);
        });

        Schema::table('marketing_ad_accounts', function (Blueprint $table) {
            $table->dropIndex('marketing_ad_accounts_connection_index');
            $table->dropColumn(['provider', 'history_from']);
        });

        Schema::table('marketing_ad_connections', function (Blueprint $table) {
            $table->dropIndex('marketing_ad_connections_provider_email_index');
            $table->dropColumn('account_email');
            $table->unsignedBigInteger('office_id')->nullable();
            $table->unsignedInteger('clinic_num')->nullable();
            $table->index(['provider', 'office_id', 'clinic_num'], 'marketing_ad_connections_location_index');
        });
    }
};
