<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a developer copy their key again after re-entering their password. The key is kept
 * encrypted with the app key (APP_KEY); lookups still go through the SHA-256 token_hash.
 * Keys issued before this column existed have no copy and can only be regenerated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->text('token_encrypted')->nullable()->after('token_hint');
        });
    }

    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn('token_encrypted');
        });
    }
};
