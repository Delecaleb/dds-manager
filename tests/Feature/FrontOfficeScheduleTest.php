<?php

namespace Tests\Feature;

use App\Models\OdAdjustment;
use App\Models\OdAppointment;
use App\Models\OdCarrier;
use App\Models\OdInsplan;
use App\Models\OdPatient;
use App\Models\OdProcedure;
use App\Models\OdProcedureLog;
use App\Models\OdProvider;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class FrontOfficeScheduleTest extends TestCase
{
    public function test_front_office_stats_returns_net_production(): void
    {
        $user = User::factory()->create();

        $monthYear = Carbon::now()->format('Y-m');
        $today = Carbon::today()->format('Y-m-d');

        // Create completed procedure ($1000 gross)
        OdProcedureLog::create([
            'office_id' => 1,
            'ProcNum' => 9901,
            'PatNum' => 1,
            'ProcDate' => $today,
            'ProcFee' => 1000.00,
            'ProcStatus' => 2, // Completed
        ]);

        // Create adjustment (-$200)
        OdAdjustment::create([
            'office_id' => 1,
            'AdjNum' => 9901,
            'PatNum' => 1,
            'AdjDate' => $today,
            'AdjAmt' => -200.00,
        ]);

        $response = $this->actingAs($user)->getJson(route('front-office.stats', [
            'month_year' => $monthYear,
        ]));

        $response->assertStatus(200);

        // Net production should be 1000 + (-200) = 800.00
        $data = $response->json();
        $this->assertEquals(800.00, $data['monthly']['actual']);
    }

    public function test_schedule_table_subtab_endpoints_respond(): void
    {
        $user = User::factory()->create();
        $monthYear = Carbon::now()->format('Y-m');

        // Test broken appointments endpoint
        $resBroken = $this->actingAs($user)->getJson(route('front-office.broken-appointments', ['month_year' => $monthYear]));
        $resBroken->assertStatus(200);

        // Test hygiene recall due endpoint
        $resRecall = $this->actingAs($user)->getJson(route('front-office.hygiene-recall-due', ['month_year' => $monthYear]));
        $resRecall->assertStatus(200);

        // Test unscheduled treatment endpoint
        $resUnscheduled = $this->actingAs($user)->getJson(route('front-office.unscheduled-treatment', ['month_year' => $monthYear]));
        $resUnscheduled->assertStatus(200);

        // Test hygiene reappoint endpoint
        $resReappoint = $this->actingAs($user)->getJson(route('front-office.hygiene-reappoint', ['month_year' => $monthYear]));
        $resReappoint->assertStatus(200);
    }

    public function test_broken_appointments_resolves_next_visit_date_insurance_and_amount(): void
    {
        $user = User::factory()->create();

        $carrier = OdCarrier::create([
            'CarrierNum' => 88,
            'CarrierName' => 'Delta Dental of MI',
        ]);

        $insPlan = OdInsplan::create([
            'PlanNum' => 401,
            'CarrierNum' => $carrier->CarrierNum,
        ]);

        $patient = OdPatient::create([
            'PatNum' => 101,
            'FName' => 'Jane',
            'LName' => 'Doe',
            'WirelessPhone' => '3135559876',
            'Email' => 'jane.doe@example.com',
        ]);

        $provider = OdProvider::create([
            'ProvNum' => 95,
            'LName' => 'Haddow',
            'PName' => 'Mason',
            'Abbr' => 'HADD',
        ]);

        $proc = OdProcedure::create([
            'CodeNum' => 201,
            'ProcCode' => 'D8090',
            'Descript' => 'comprehensive orthodontic treatment',
        ]);

        // Broken appointment in August
        $brokenApt = OdAppointment::create([
            'AptNum' => 98881,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'InsPlan1' => $insPlan->PlanNum,
            'AptStatus' => 5, // Broken
            'IsNewPatient' => 0,
            'AptDateTime' => '2026-08-10 10:00:00',
            'Note' => 'Left voicemail',
        ]);

        // Attached procedure fee
        OdProcedureLog::create([
            'ProcNum' => 988811,
            'PatNum' => $patient->PatNum,
            'AptNum' => $brokenApt->AptNum,
            'CodeNum' => $proc->CodeNum,
            'ProcDate' => '2026-08-10',
            'ProcStatus' => '1',
            'ProcFee' => 4500.00,
        ]);

        // Future re-booked appointment in September
        OdAppointment::create([
            'AptNum' => 98882,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'AptStatus' => 1, // Scheduled
            'IsNewPatient' => 0,
            'AptDateTime' => '2026-09-15 14:00:00',
        ]);

        $response = $this->actingAs($user)->getJson(route('front-office.broken-appointments', [
            'month_year' => '2026-08',
        ]));

        $response->assertOk()
            ->assertJsonFragment(['patient_name' => 'Jane Doe'])
            ->assertJsonFragment(['provider_name' => 'Mason Haddow'])
            ->assertJsonFragment(['insurance_carrier' => 'Delta Dental of MI'])
            ->assertJsonFragment(['amount' => '$ 4,500.00'])
            ->assertJsonFragment(['mobile_phone' => '(313)555-9876'])
            ->assertJsonFragment(['next_visit_date' => '2026-09-15'])
            ->assertJsonFragment(['description' => 'comprehensive orthodontic treatment'])
            ->assertJsonFragment(['status' => 'UNSCHEDULED']);
    }
}
