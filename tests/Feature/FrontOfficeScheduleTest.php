<?php

namespace Tests\Feature;

use App\Models\OdAdjustment;
use App\Models\OdProcedureLog;
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
}
