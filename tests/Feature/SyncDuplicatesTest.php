<?php

namespace Tests\Feature;

use App\Models\OdPatientBalance;
use App\Models\OdProvider;
use App\Models\Office;
use App\Models\PaySplit;
use App\Services\Sync\PatientBalanceSyncService;
use Illuminate\Database\UniqueConstraintViolationException;
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

        $service = app(PatientBalanceSyncService::class)->forOffice($office);

        // First sync run
        $service->sync();

        $countRun1 = OdPatientBalance::withoutGlobalScopes()->where('office_id', $office->id)->where('PatNum', 101)->count();
        $this->assertEquals(1, $countRun1);

        // Second sync run (simulating re-run)
        $service->sync();

        $countRun2 = OdPatientBalance::withoutGlobalScopes()->where('office_id', $office->id)->where('PatNum', 101)->count();
        $this->assertEquals(1, $countRun2, 'Patient balance sync inserted duplicate records for guarantor PatNum 101');
    }

    public function test_pay_split_model_has_primary_key_and_no_incrementing(): void
    {
        $paySplit = new PaySplit;

        $this->assertEquals('SplitNum', $paySplit->getKeyName());
        $this->assertFalse($paySplit->getIncrementing());
    }

    public function test_od_pay_splits_table_enforces_unique_splitnum_per_office(): void
    {
        $office1 = Office::create(['name' => 'Office 1']);
        $office2 = Office::create(['name' => 'Office 2']);

        // Inserting same SplitNum for office 1 and office 2 should succeed
        \DB::table('od_pay_splits')->insert([
            'office_id' => $office1->id,
            'SplitNum' => 5001,
            'SplitAmt' => '100.00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('od_pay_splits')->insert([
            'office_id' => $office2->id,
            'SplitNum' => 5001,
            'SplitAmt' => '200.00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseCount('od_pay_splits', 2);

        // Attempting to insert duplicate SplitNum for the SAME office should throw UniqueConstraintViolationException
        $this->expectException(UniqueConstraintViolationException::class);

        \DB::table('od_pay_splits')->insert([
            'office_id' => $office1->id,
            'SplitNum' => 5001,
            'SplitAmt' => '150.00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
