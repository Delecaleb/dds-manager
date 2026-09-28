<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI Website Builder (Growth Engine).
 *
 *   site_builds          — a website project: the brief (business, brand, pages, SEO).
 *   site_build_versions  — one generation run; snapshots the brief it was built from and
 *                          holds the design system (stylesheet, header, footer).
 *   site_build_pages     — one generated page of a version.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_builds', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('domain', 190)->nullable();
            $table->foreignId('marketing_site_id')->nullable()->constrained('marketing_sites')->nullOnDelete();
            $table->json('business');
            $table->json('brand');
            $table->string('logo_path')->nullable();
            $table->json('pages');
            $table->json('seo');
            $table->text('instructions')->nullable();
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('site_build_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_build_id')->constrained('site_builds')->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('status', 20)->default('queued');
            $table->string('scope', 10)->default('site');
            $table->string('scope_path', 150)->nullable();
            $table->text('revision_notes')->nullable();
            $table->json('brief');
            $table->json('design')->nullable();
            $table->text('error')->nullable();
            $table->unsignedBigInteger('input_tokens')->default(0);
            $table->unsignedBigInteger('output_tokens')->default(0);
            $table->unsignedBigInteger('cache_read_tokens')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['site_build_id', 'number']);
        });

        Schema::create('site_build_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_build_version_id')->constrained('site_build_versions')->cascadeOnDelete();
            $table->string('path', 150);
            $table->string('title', 120);
            $table->string('status', 20)->default('pending');
            $table->json('meta')->nullable();
            $table->longText('body_html')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['site_build_version_id', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_build_pages');
        Schema::dropIfExists('site_build_versions');
        Schema::dropIfExists('site_builds');
    }
};
