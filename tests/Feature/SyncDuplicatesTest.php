<?php

namespace Tests\Feature;

use App\Models\OdPatientBalance;
use App\Models\OdProvider;
use App\Models\Office;
use App\Services\Sync\PatientBalanceSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncDuplicatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_model_has_primary_key_and_no_incrementing(): void
    {
        $provider = new OdProvider;

        $this->assertEquals('ProvNum', $provider->getKeyName());
        $this->assertFalse($provider->getIncrementing());
    }

    public function test_patient_balance_sync_does_not_create_duplicate_records(): void
    {
        $office = Office::create(['name' => 'Main Clinic']);

        // Insert patient records
        \DB::table('od_patients')->insert([
            ['office_id' => $office->id, 'PatNum' => 101, 'Guarantor' => 101, 'BalTotal' => '150.00', 'created_at' => now(), 'updated_at' => now()],
            ['office_id' => $office->id, 'PatNum' => 102, 'Guarantor' => 101, 'BalTotal' => '50.00', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $service = app(PatientBalanceSyncService::class);

        // First sync run
        $service->sync();

        $countRun1 = OdPatientBalance::where('office_id', $office->id)->where('PatNum', 101)->count();
        $this->assertEquals(1, $countRun1);

        // Second sync run (simulating re-run)
        $service->sync();

        $countRun2 = OdPatientBalance::where('office_id', $office->id)->where('PatNum', 101)->count();
        $this->assertEquals(1, $countRun2, 'Patient balance sync inserted duplicate records for guarantor PatNum 101');
    }
}
