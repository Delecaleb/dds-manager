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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('developer_key')->nullable();
            $table->text('customer_key')->nullable();
            $table->string('api_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default office using existing config/env keys if available
        DB::table('offices')->insert([
            'id' => 1,
            'name' => '8 Mile',
            'developer_key' => config('opendental.developer_key'),
            'customer_key' => config('opendental.customer_key'),
            'api_url' => config('opendental.url'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
