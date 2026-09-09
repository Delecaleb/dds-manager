<?php

namespace Tests\Feature;

use App\Models\OdCarrier;
use App\Models\OdDeposit;
use App\Models\OdInsplan;
use App\Models\OdPatientBalance;
use App\Models\OdPayment;
use App\Models\OdPayPlanCharge;
use App\Models\OdRecall;
use App\Models\OdRecallType;
use App\Models\OdSchedule;
use App\Models\OdTreatmentPlanAttachments;
use App\Models\Office;
use App\Models\SyncLog;
use App\Models\TreatmentPlan;
use App\Services\OpenDental\QueryService;
use App\Services\Sync\PatientBalanceSyncService;
use App\Services\Sync\SyncCarrierService;
use Mockery\MockInterface;
use Tests\TestCase;

class MultiOfficeSyncTest extends TestCase
{
    public function test_multiple_offices_can_store_same_primary_key_without_collision(): void
    {
        $office1 = Office::create(['name' => 'Office North', 'is_active' => true]);
        $office2 = Office::create(['name' => 'Office South', 'is_active' => true]);

        // 1. OdPayment
        $pay1 = OdPayment::create(['office_id' => $office1->id, 'PayNum' => 9999, 'PayAmt' => 100.00]);
        $pay2 = OdPayment::create(['office_id' => $office2->id, 'PayNum' => 9999, 'PayAmt' => 200.00]);
        $this->assertDatabaseHas('od_payments', ['office_id' => $office1->id, 'PayNum' => 9999, 'PayAmt' => 100.00]);
        $this->assertDatabaseHas('od_payments', ['office_id' => $office2->id, 'PayNum' => 9999, 'PayAmt' => 200.00]);

        // 2. OdCarrier
        $carrier1 = OdCarrier::create(['office_id' => $office1->id, 'CarrierNum' => 8888, 'CarrierName' => 'Delta North']);
        $carrier2 = OdCarrier::create(['office_id' => $office2->id, 'CarrierNum' => 8888, 'CarrierName' => 'Delta South']);
        $this->assertDatabaseHas('od_carriers', ['office_id' => $office1->id, 'CarrierNum' => 8888, 'CarrierName' => 'Delta North']);
        $this->assertDatabaseHas('od_carriers', ['office_id' => $office2->id, 'CarrierNum' => 8888, 'CarrierName' => 'Delta South']);

        // 3. OdDeposit
        $dep1 = OdDeposit::create(['office_id' => $office1->id, 'DepositNum' => 7777, 'Amount' => 500.00]);
        $dep2 = OdDeposit::create(['office_id' => $office2->id, 'DepositNum' => 7777, 'Amount' => 750.00]);
        $this->assertDatabaseHas('od_deposits', ['office_id' => $office1->id, 'DepositNum' => 7777, 'Amount' => 500.00]);
        $this->assertDatabaseHas('od_deposits', ['office_id' => $office2->id, 'DepositNum' => 7777, 'Amount' => 750.00]);

        // 4. OdInsplan
        $ins1 = OdInsplan::create(['office_id' => $office1->id, 'PlanNum' => 6666, 'PlanType' => 'PPO']);
        $ins2 = OdInsplan::create(['office_id' => $office2->id, 'PlanNum' => 6666, 'PlanType' => 'HMO']);
        $this->assertDatabaseHas('od_insplans', ['office_id' => $office1->id, 'PlanNum' => 6666, 'PlanType' => 'PPO']);
        $this->assertDatabaseHas('od_insplans', ['office_id' => $office2->id, 'PlanNum' => 6666, 'PlanType' => 'HMO']);

        // 5. OdPayPlanCharge
        $ppcData1 = [
            'office_id' => $office1->id,
            'PayPlanChargeNum' => 5555,
            'PayPlanNum' => 101,
            'Guarantor' => 101,
            'PatNum' => 101,
            'ChargeDate' => '2026-08-01',
            'Principal' => '50.00',
            'Interest' => '0',
            'Note' => '',
            'ProvNum' => 1,
            'ClinicNum' => 1,
            'ChargeType' => 0,
            'ProcNum' => 0,
            'SecDateTEntry' => '2026-08-01',
            'SecDateTEdit' => '',
            'StatementNum' => 0,
            'FKey' => 0,
            'LinkType' => 0,
            'IsOffset' => 0,
            'IsDownPayment' => 0,
        ];
        $ppcData2 = array_merge($ppcData1, ['office_id' => $office2->id, 'Principal' => '75.00']);
        OdPayPlanCharge::create($ppcData1);
        OdPayPlanCharge::create($ppcData2);
        $this->assertDatabaseHas('od_pay_plan_charges', ['office_id' => $office1->id, 'PayPlanChargeNum' => 5555, 'Principal' => '50.00']);
        $this->assertDatabaseHas('od_pay_plan_charges', ['office_id' => $office2->id, 'PayPlanChargeNum' => 5555, 'Principal' => '75.00']);

        // 6. OdRecall
        $recData1 = [
            'office_id' => $office1->id,
            'RecallNum' => 4444,
            'PatNum' => 101,
            'DateDueCalc' => '2026-08-01',
            'DateDue' => '2026-08-01',
            'DatePrevious' => '2026-02-01',
            'RecallInterval' => 6,
            'RecallStatus' => 0,
            'Note' => '',
            'IsDisabled' => 0,
            'DateTStamp' => '2026-08-01 10:00:00',
            'RecallTypeNum' => 1,
            'DisableUntilBalance' => '0',
            'DisableUntilDate' => '2026-08-01',
            'DateScheduled' => '2026-08-01',
            'Priority' => 0,
            'TimePatternOverride' => '',
        ];
        $recData2 = array_merge($recData1, ['office_id' => $office2->id, 'PatNum' => 202]);
        OdRecall::create($recData1);
        OdRecall::create($recData2);
        $this->assertDatabaseHas('od_recalls', ['office_id' => $office1->id, 'RecallNum' => 4444, 'PatNum' => 101]);
        $this->assertDatabaseHas('od_recalls', ['office_id' => $office2->id, 'RecallNum' => 4444, 'PatNum' => 202]);

        // 7. OdRecallType
        $rtData1 = [
            'office_id' => $office1->id,
            'RecallTypeNum' => 3333,
            'Description' => 'Type North',
            'DefaultInterval' => 6,
            'TimePattern' => '//X//',
            'Procedures' => 'D0120',
            'AppendToSpecial' => 0,
        ];
        $rtData2 = array_merge($rtData1, ['office_id' => $office2->id, 'Description' => 'Type South']);
        OdRecallType::create($rtData1);
        OdRecallType::create($rtData2);
        $this->assertDatabaseHas('od_recall_types', ['office_id' => $office1->id, 'RecallTypeNum' => 3333, 'Description' => 'Type North']);
        $this->assertDatabaseHas('od_recall_types', ['office_id' => $office2->id, 'RecallTypeNum' => 3333, 'Description' => 'Type South']);

        // 8. OdSchedule
        $sch1 = OdSchedule::create(['office_id' => $office1->id, 'ScheduleNum' => 2222, 'ProvNum' => 1]);
        $sch2 = OdSchedule::create(['office_id' => $office2->id, 'ScheduleNum' => 2222, 'ProvNum' => 2]);
        $this->assertDatabaseHas('od_schedules', ['office_id' => $office1->id, 'ScheduleNum' => 2222, 'ProvNum' => 1]);
        $this->assertDatabaseHas('od_schedules', ['office_id' => $office2->id, 'ScheduleNum' => 2222, 'ProvNum' => 2]);

        // 9. TreatmentPlan & OdTreatmentPlanAttachments
        $tp1 = TreatmentPlan::create(['office_id' => $office1->id, 'TreatPlanNum' => 1111, 'Heading' => 'TP North']);
        $tp2 = TreatmentPlan::create(['office_id' => $office2->id, 'TreatPlanNum' => 1111, 'Heading' => 'TP South']);
        $this->assertDatabaseHas('treatment_plans', ['office_id' => $office1->id, 'TreatPlanNum' => 1111, 'Heading' => 'TP North']);
        $this->assertDatabaseHas('treatment_plans', ['office_id' => $office2->id, 'TreatPlanNum' => 1111, 'Heading' => 'TP South']);

        $tpa1 = OdTreatmentPlanAttachments::create(['office_id' => $office1->id, 'TreatPlanAttachNum' => 1212, 'TreatPlanNum' => 1111]);
        $tpa2 = OdTreatmentPlanAttachments::create(['office_id' => $office2->id, 'TreatPlanAttachNum' => 1212, 'TreatPlanNum' => 1111]);
        $this->assertDatabaseHas('od_treatment_plan_attachments', ['office_id' => $office1->id, 'TreatPlanAttachNum' => 1212]);
        $this->assertDatabaseHas('od_treatment_plan_attachments', ['office_id' => $office2->id, 'TreatPlanAttachNum' => 1212]);
    }

    public function test_sync_service_isolates_sync_logs_and_upserts_per_office(): void
    {
        $office1 = Office::create(['name' => 'Office One', 'is_active' => true]);
        $office2 = Office::create(['name' => 'Office Two', 'is_active' => true]);

        $this->mock(QueryService::class, function (MockInterface $mock) {
            $mock->shouldReceive('forOffice')->andReturnSelf();
            $mock->shouldReceive('shortQuery')
                ->andReturn(
                    [
                        [
                            'CarrierNum' => 701,
                            'CarrierName' => 'Office 1 Insurer',
                            'DateTStamp' => '2026-08-01 10:00:00',
                        ],
                    ],
                    [],
                    [
                        [
                            'CarrierNum' => 701,
                            'CarrierName' => 'Office 2 Insurer',
                            'DateTStamp' => '2026-08-01 11:00:00',
                        ],
                    ],
                    []
                );
        });

        // Sync Office 1
        $service1 = app(SyncCarrierService::class)->forOffice($office1);
        $service1->sync();

        // Sync Office 2
        $service2 = app(SyncCarrierService::class)->forOffice($office2);
        $service2->sync();

        // Check SyncLog partitioning
        $log1 = SyncLog::withoutGlobalScopes()->where('module', "office_{$office1->id}:carrier")->first();
        $log2 = SyncLog::withoutGlobalScopes()->where('module', "office_{$office2->id}:carrier")->first();

        $this->assertNotNull($log1);
        $this->assertNotNull($log2);
        $this->assertEquals($office1->id, $log1->office_id);
        $this->assertEquals($office2->id, $log2->office_id);

        // Check both records exist without overwriting each other
        $c1 = OdCarrier::withoutGlobalScopes()->where('office_id', $office1->id)->where('CarrierNum', 701)->first();
        $c2 = OdCarrier::withoutGlobalScopes()->where('office_id', $office2->id)->where('CarrierNum', 701)->first();

        $this->assertNotNull($c1);
        $this->assertNotNull($c2);
        $this->assertEquals('Office 1 Insurer', $c1->CarrierName);
        $this->assertEquals('Office 2 Insurer', $c2->CarrierName);
    }

    public function test_patient_balance_sync_is_scoped_per_office(): void
    {
        $office1 = Office::create(['name' => 'Office Alpha', 'is_active' => true]);
        $office2 = Office::create(['name' => 'Office Beta', 'is_active' => true]);

        // Office 1 patient
        \DB::table('od_patients')->insert([
            'office_id' => $office1->id,
            'PatNum' => 501,
            'Guarantor' => 501,
            'BalTotal' => '300.00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Office 2 patient with same PatNum
        \DB::table('od_patients')->insert([
            'office_id' => $office2->id,
            'PatNum' => 501,
            'Guarantor' => 501,
            'BalTotal' => '700.00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run sync for Office 1
        app(PatientBalanceSyncService::class)->forOffice($office1)->sync();

        $bal1 = OdPatientBalance::withoutGlobalScopes()->where('office_id', $office1->id)->where('PatNum', 501)->first();
        $this->assertNotNull($bal1);
        $this->assertEquals('300.00', $bal1->Total);

        // Run sync for Office 2
        app(PatientBalanceSyncService::class)->forOffice($office2)->sync();

        $bal2 = OdPatientBalance::withoutGlobalScopes()->where('office_id', $office2->id)->where('PatNum', 501)->first();
        $this->assertNotNull($bal2);
        $this->assertEquals('700.00', $bal2->Total);
    }

    public function test_cli_command_syncs_all_active_offices_when_office_id_omitted(): void
    {
        $office1 = Office::create(['name' => 'Active Office 1', 'is_active' => true]);
        $office2 = Office::create(['name' => 'Active Office 2', 'is_active' => true]);
        $officeInactive = Office::create(['name' => 'Inactive Office', 'is_active' => false]);

        $this->mock(QueryService::class, function (MockInterface $mock) {
            $mock->shouldReceive('forOffice')->andReturnSelf();
            $mock->shouldReceive('shortQuery')->andReturn([]);
        });

        $this->artisan('sync:payment')
            ->expectsOutputToContain("Syncing payments for office [{$office1->id}]")
            ->expectsOutputToContain("Syncing payments for office [{$office2->id}]")
            ->doesntExpectOutput("Syncing payments for office [{$officeInactive->id}]")
            ->assertSuccessful();
    }

    public function test_cli_command_syncs_specific_office_when_office_id_provided(): void
    {
        $office1 = Office::create(['name' => 'Office One', 'is_active' => true]);
        $office2 = Office::create(['name' => 'Office Two', 'is_active' => true]);

        $this->mock(QueryService::class, function (MockInterface $mock) {
            $mock->shouldReceive('forOffice')->andReturnSelf();
            $mock->shouldReceive('shortQuery')->andReturn([]);
        });

        $this->artisan('sync:payment', ['--office-id' => $office2->id])
            ->expectsOutputToContain("Syncing payments for office [{$office2->id}]")
            ->doesntExpectOutput("Syncing payments for office [{$office1->id}]")
            ->assertSuccessful();
    }

    public function test_cli_command_fault_isolation_continues_syncing_subsequent_offices_when_one_office_fails(): void
    {
        $office1 = Office::create(['name' => 'Failing Office 1', 'is_active' => true]);
        $office2 = Office::create(['name' => 'Healthy Office 2', 'is_active' => true]);

        $this->mock(QueryService::class, function (MockInterface $mock) use ($office1, $office2) {
            $mock->shouldReceive('forOffice')->withArgs(fn ($arg) => $arg && $arg->id === $office1->id)->andReturnSelf();
            $mock->shouldReceive('forOffice')->withArgs(fn ($arg) => $arg && $arg->id === $office2->id)->andReturnSelf();

            // Office 1 fails with network error on all retries
            $mock->shouldReceive('shortQuery')->andReturnUsing(function () {
                static $called = 0;
                $called++;
                if ($called <= 3) {
                    throw new \Exception('OpenDental connection timed out');
                }

                return [];
            });
        });

        $this->artisan('sync:payment')
            ->expectsOutputToContain("Syncing payments for office [{$office1->id}]")
            ->expectsOutputToContain("Syncing payments for office [{$office2->id}]")
            ->assertSuccessful();
    }
}
