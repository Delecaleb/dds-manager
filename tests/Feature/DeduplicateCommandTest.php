<?php

namespace Tests\Feature;

use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeduplicateCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_deduplicate_command_executes_successfully(): void
    {
        $office = Office::create(['name' => 'Test Practice']);

        // Insert distinct records
        DB::table('od_providers')->insert([
            ['office_id' => $office->id, 'ProvNum' => 5, 'LName' => 'Smith', 'created_at' => now(), 'updated_at' => now()],
            ['office_id' => $office->id, 'ProvNum' => 6, 'LName' => 'Jones', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Dry-run mode for a specific table
        $this->artisan('sync:deduplicate', ['table' => 'od_providers', '--dry-run' => true])
            ->assertExitCode(0);

        // Execution mode for all tables
        $this->artisan('sync:deduplicate', ['table' => 'all'])
            ->assertExitCode(0);

        $this->assertEquals(2, DB::table('od_providers')->where('office_id', $office->id)->count());
    }

    public function test_deduplicate_command_removes_pre_existing_duplicates(): void
    {
        $office = Office::create(['name' => 'Duplicate Practice']);

        // Temporarily drop index in SQLite memory DB to simulate legacy duplicate data insertion
        try {
            DB::statement('DROP INDEX IF EXISTS od_appointments_office_aptnum_unique');
        } catch (\Throwable $e) {
        }

        DB::table('od_appointments')->insert([
            ['office_id' => $office->id, 'AptNum' => 301, 'PatNum' => 201, 'created_at' => now(), 'updated_at' => now()],
            ['office_id' => $office->id, 'AptNum' => 301, 'PatNum' => 201, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->assertEquals(2, DB::table('od_appointments')->where('office_id', $office->id)->where('AptNum', 301)->count());

        $this->artisan('sync:deduplicate', ['table' => 'od_appointments'])
            ->assertExitCode(0);

        $this->assertEquals(1, DB::table('od_appointments')->where('office_id', $office->id)->where('AptNum', 301)->count());
    }
}
