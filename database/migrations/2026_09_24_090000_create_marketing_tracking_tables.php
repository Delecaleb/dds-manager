<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Growth Engine web tracking.
 *
 * A visitor arrives anonymously, browses, and may later identify themselves by signing up.
 * These four tables keep that chain: site → visitor → session → event, with first-touch
 * attribution held on the visitor so the journey can be credited to the campaign that
 * started it, however many visits later the signup happens.
 *
 * Event volume is far higher than clinical data, so events carry only what the reports
 * need and every query path is indexed.
 *
 * Times are DATETIME, not TIMESTAMP: MySQL/MariaDB gives only one TIMESTAMP column an
 * automatic default, so a second NOT NULL TIMESTAMP is handed a zero date that strict mode
 * rejects (error 1067). Each table is created only if missing, so a run that failed
 * part-way can simply be repeated.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('marketing_sites')) {
            Schema::create('marketing_sites', function (Blueprint $table) {
                $table->id();
                $table->string('site_key', 32)->unique();      // public; identifies the site only
                $table->string('name');
                $table->string('domain');                       // reporting host, e.g. plymouthdental.com
                $table->unsignedBigInteger('office_id')->nullable(); // which office the leads belong to
                $table->boolean('is_active')->default(true);
                $table->dateTime('verified_at')->nullable();   // first event received
                $table->timestamps();

                $table->index(['is_active', 'domain']);
            });
        }

        if (! Schema::hasTable('marketing_visitors')) {
            Schema::create('marketing_visitors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('marketing_sites')->cascadeOnDelete();
                $table->uuid('visitor_uid');                    // first-party id held by the browser
                $table->dateTime('first_seen_at');
                $table->dateTime('last_seen_at');

                // First touch: the visit that introduced this person, kept even after later visits.
                $table->string('first_source')->nullable();
                $table->string('first_medium')->nullable();
                $table->string('first_campaign')->nullable();
                $table->string('first_term')->nullable();
                $table->string('first_content')->nullable();
                $table->string('first_referrer', 1024)->nullable();
                $table->string('first_landing_path', 1024)->nullable();
                $table->string('first_click_id')->nullable();   // gclid / fbclid / msclkid
                $table->string('first_click_source', 32)->nullable();

                // Set when the visitor identifies themselves. Contact details for a real person:
                // treat with the same care as patient data.
                $table->dateTime('identified_at')->nullable();
                $table->string('email')->nullable();
                $table->string('phone', 32)->nullable();
                $table->string('name')->nullable();

                $table->timestamps();

                $table->unique(['site_id', 'visitor_uid']);
                $table->index(['site_id', 'first_seen_at']);
                $table->index(['site_id', 'identified_at']);
                $table->index('email');
                $table->index('phone');
            });
        }

        if (! Schema::hasTable('marketing_sessions')) {
            Schema::create('marketing_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('marketing_sites')->cascadeOnDelete();
                $table->foreignId('visitor_id')->constrained('marketing_visitors')->cascadeOnDelete();
                $table->uuid('session_uid');
                $table->dateTime('started_at');
                $table->dateTime('last_event_at');

                // Attribution of this visit (may differ from the visitor's first touch).
                $table->string('source')->nullable();
                $table->string('medium')->nullable();
                $table->string('campaign')->nullable();
                $table->string('term')->nullable();
                $table->string('content')->nullable();
                $table->string('referrer', 1024)->nullable();
                $table->string('landing_path', 1024)->nullable();
                $table->string('click_id')->nullable();
                $table->string('click_source', 32)->nullable();

                $table->string('device', 16)->nullable();       // desktop | mobile | tablet
                $table->string('browser', 32)->nullable();
                $table->unsignedInteger('pageviews')->default(0);
                $table->boolean('has_signup')->default(false);
                $table->timestamps();

                $table->unique(['site_id', 'session_uid']);
                $table->index(['site_id', 'started_at']);
                $table->index(['visitor_id', 'started_at']);
            });
        }

        if (! Schema::hasTable('marketing_events')) {
            Schema::create('marketing_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('marketing_sites')->cascadeOnDelete();
                $table->foreignId('visitor_id')->constrained('marketing_visitors')->cascadeOnDelete();
                $table->foreignId('session_id')->constrained('marketing_sessions')->cascadeOnDelete();
                $table->string('type', 32);                     // pageview | signup | custom name
                $table->string('path', 1024)->nullable();
                $table->string('title')->nullable();
                $table->string('referrer', 1024)->nullable();
                $table->json('payload')->nullable();            // what the site passed with the event
                $table->dateTime('occurred_at');
                $table->timestamps();

                $table->index(['site_id', 'type', 'occurred_at']);
                $table->index(['visitor_id', 'occurred_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_events');
        Schema::dropIfExists('marketing_sessions');
        Schema::dropIfExists('marketing_visitors');
        Schema::dropIfExists('marketing_sites');
    }
};
