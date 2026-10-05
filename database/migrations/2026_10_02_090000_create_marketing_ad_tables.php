<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Growth Engine ad platforms (Google Ads first; Meta reuses the same tables).
 *
 * connection → account → campaign → one stats row per campaign per day. Spend is kept in
 * micros (1,000,000 = one unit of the account's currency), exactly as the platform reports
 * it, so nothing is lost to rounding before a report sums it.
 *
 * Follows the tracking tables: DATETIME rather than TIMESTAMP, and each table is created
 * only if missing so a run that failed part-way can simply be repeated.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('marketing_ad_connections')) {
            Schema::create('marketing_ad_connections', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 32)->unique();       // google_ads | meta
                $table->string('status', 16)->default('connected'); // connected | error | disconnected
                $table->text('refresh_token')->nullable();      // encrypted by the model cast
                $table->unsignedBigInteger('connected_by')->nullable(); // users.id
                $table->dateTime('connected_at')->nullable();
                $table->dateTime('last_synced_at')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('marketing_ad_accounts')) {
            Schema::create('marketing_ad_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('connection_id')->constrained('marketing_ad_connections')->cascadeOnDelete();
                $table->string('external_id', 32);              // Google Ads customer id, digits only
                $table->string('login_customer_id', 32)->nullable(); // manager account it is reached through
                $table->string('name')->nullable();
                $table->string('currency_code', 8)->nullable();
                $table->string('time_zone', 64)->nullable();
                $table->boolean('is_manager')->default(false);  // managers hold no campaigns of their own
                $table->string('status', 32)->nullable();       // platform status, e.g. ENABLED
                $table->unsignedBigInteger('office_id')->nullable(); // default location for its campaigns
                $table->boolean('is_enabled')->default(true);   // false = keep the row, stop syncing it
                $table->dateTime('last_synced_at')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();

                $table->unique(['connection_id', 'external_id']);
                $table->index('office_id');
            });
        }

        if (! Schema::hasTable('marketing_ad_campaigns')) {
            Schema::create('marketing_ad_campaigns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_id')->constrained('marketing_ad_accounts')->cascadeOnDelete();
                $table->string('external_id', 32);
                $table->string('name');
                $table->string('status', 32)->nullable();       // ENABLED | PAUSED | REMOVED
                $table->string('channel_type', 48)->nullable(); // SEARCH | PERFORMANCE_MAX | ...
                $table->unsignedBigInteger('daily_budget_micros')->nullable();
                $table->unsignedBigInteger('office_id')->nullable(); // overrides the account's office
                $table->timestamps();

                $table->unique(['account_id', 'external_id']);
                $table->index('office_id');
            });
        }

        if (! Schema::hasTable('marketing_ad_campaign_stats')) {
            Schema::create('marketing_ad_campaign_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('marketing_ad_campaigns')->cascadeOnDelete();
                $table->date('date');                           // in the ad account's time zone
                $table->unsignedBigInteger('impressions')->default(0);
                $table->unsignedBigInteger('clicks')->default(0);
                $table->unsignedBigInteger('cost_micros')->default(0);
                $table->decimal('conversions', 14, 4)->default(0);       // as counted by the platform
                $table->decimal('conversions_value', 16, 4)->default(0);
                $table->timestamps();

                $table->unique(['campaign_id', 'date']);
                $table->index('date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_ad_campaign_stats');
        Schema::dropIfExists('marketing_ad_campaigns');
        Schema::dropIfExists('marketing_ad_accounts');
        Schema::dropIfExists('marketing_ad_connections');
    }
};
