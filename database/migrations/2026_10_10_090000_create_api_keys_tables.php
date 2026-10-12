<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Developer API: read-only keys issued per location, and an access log of every request
 * made with them (the API returns patient contact details, so every read is recorded).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // The one location the key can read (ClinicRegistry key: office, or office + clinic).
            // No foreign key, like every other office_id in this schema: `offices` is not
            // InnoDB on every install, and MySQL refuses a constraint to a MyISAM table.
            $table->unsignedBigInteger('office_id');
            $table->unsignedBigInteger('clinic_num')->nullable();
            // Only a SHA-256 of the key is stored; the key itself is shown once, at creation.
            $table->char('token_hash', 64)->unique();
            $table->string('token_hint', 16);
            $table->json('abilities');
            $table->unsignedSmallInteger('rate_limit_per_minute');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['office_id', 'clinic_num']);
        });

        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_key_id')->constrained('api_keys')->cascadeOnDelete();
            $table->string('method', 10);
            $table->string('path');
            $table->text('query')->nullable();
            $table->unsignedSmallInteger('status');
            $table->unsignedInteger('rows')->nullable();
            $table->string('ip', 45)->nullable();
            $table->unsignedInteger('duration_ms');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['api_key_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
        Schema::dropIfExists('api_keys');
    }
};
