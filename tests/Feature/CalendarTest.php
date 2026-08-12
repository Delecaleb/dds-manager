<?php

namespace Tests\Feature;

use App\Models\OdAdjustment;
use App\Models\OdAppointment;
use App\Models\OdProcedureLog;
use App\Models\OdProvider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_calendar_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)->get(route('calendar.index'));
        $response->assertOk();
    }

    public function test_calendar_resources_endpoint_returns_operatory_list(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.resources', ['active_only' => '0']));

        $response->assertOk()
            ->assertJsonCount(10)
            ->assertJsonFragment(['id' => 'op-1', 'title' => 'DR-1'])
            ->assertJsonFragment(['id' => 'op-5', 'title' => 'DR-5'])
            ->assertJsonFragment(['id' => 'op-10', 'title' => 'Unassigned 10']);
    }

    public function test_calendar_resources_active_only_filtering(): void
    {
        // Add one test appointment in operatory 2 (DR-2)
        $provider = OdProvider::create([
            'ProvNum' => 81,
            'LName' => 'Elias',
            'PName' => 'Kathy',
            'Abbr' => 'ELIAS',
        ]);

        OdAppointment::create([
            'AptNum' => 99991,
            'PatNum' => 1,
            'AptStatus' => 1,
            'Pattern' => '//',
            'Op' => 2,
            'ProvNum' => $provider->ProvNum,
            'AptDateTime' => '2026-07-14 10:00:00',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.resources', ['date' => '2026-07-14', 'active_only' => '1']));

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => 'op-2', 'title' => 'DR-2']);
    }

    public function test_calendar_stats_returns_aggregated_values_and_providers(): void
    {
        $provider = OdProvider::create([
            'ProvNum' => 81,
            'LName' => 'Elias',
            'PName' => 'Kathy',
            'Abbr' => 'ELIAS',
        ]);

        OdAppointment::create([
            'AptNum' => 99992,
            'PatNum' => 1,
            'AptStatus' => 1,
            'Pattern' => '//',
            'Op' => 2,
            'ProvNum' => $provider->ProvNum,
            'AptDateTime' => '2026-07-14 10:00:00',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.stats', ['date' => '2026-07-14']));

        $response->assertOk()
            ->assertJsonStructure([
                'production',
                'scheduled_production',
                'providers' => [
                    '*' => [
                        'id',
                        'name',
                        'initials',
                        'specialty',
                        'count',
                        'color',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'id' => 81,
                'name' => 'Elias, Kathy',
                'initials' => 'El',
                'specialty' => 'Invis',
                'count' => 1,
                'color' => '#6DE5C1',
            ]);
    }

    public function test_calendar_stats_production_equals_net_production_for_day(): void
    {
        // Completed procedure: 200.00 gross
        OdProcedureLog::create([
            'ProcNum' => 8881,
            'PatNum' => 1,
            'ProcFee' => '200.00',
            'ProcStatus' => 'C',
            'ProcDate' => '2026-07-14 00:00:00',
        ]);

        // Adjustment: -20.00
        OdAdjustment::create([
            'AdjNum' => 8881,
            'PatNum' => 1,
            'AdjAmt' => '-20.00',
            'AdjDate' => '2026-07-14 00:00:00',
        ]);

        // WriteOff: 30.00
        DB::table('od_claim_procs')->insert([
            'ClaimProcNum' => 8881,
            'ClaimNum' => 8881,
            'PatNum' => 1,
            'ProcNum' => 8881,
            'ProcDate' => '2026-07-14 00:00:00',
            'WriteOff' => 30.00,
            'Status' => '1',
            'ClaimPaymentNum' => 0,
            'InsPayAmt' => 0,
        ]);

        // Net production = 200.00 + (-20.00) - 30.00 = 150.00
        // Scheduled production = 200.00 gross
        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.stats', ['date' => '2026-07-14']));

        $response->assertOk()
            ->assertJsonFragment([
                'production' => 150.0,
                'scheduled_production' => 200.0,
            ]);
    }

    public function test_calendar_scheduled_production_breakdown_endpoint_returns_data(): void
    {
        $provider = OdProvider::create([
            'ProvNum' => 81,
            'LName' => 'Elias',
            'PName' => 'Kathy',
            'Abbr' => 'ELIAS',
        ]);

        OdAppointment::create([
            'AptNum' => 99993,
            'PatNum' => 1,
            'AptStatus' => 1,
            'Pattern' => '//',
            'Op' => 2,
            'ProvNum' => $provider->ProvNum,
            'AptDateTime' => '2026-07-14 10:00:00',
            'ProcDescript' => 'PeriodicX',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.scheduled-production-breakdown', ['date' => '2026-07-14']));

        $response->assertOk()
            ->assertJsonStructure([
                'date',
                'total_scheduled',
                'appointment_count',
                'by_provider',
                'by_procedure',
                'appointments',
            ]);
    }

    public function test_calendar_monthly_summary_endpoint_returns_daily_breakdown(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.monthly-summary', ['start' => '2026-07-01', 'end' => '2026-07-05']));

        $response->assertOk()
            ->assertJsonStructure([
                '2026-07-01' => ['appointments', 'new_pts', 'sched', 'goal', 'prod'],
                '2026-07-02' => ['appointments', 'new_pts', 'sched', 'goal', 'prod'],
                '2026-07-03' => ['appointments', 'new_pts', 'sched', 'goal', 'prod'],
                '2026-07-04' => ['appointments', 'new_pts', 'sched', 'goal', 'prod'],
                '2026-07-05' => ['appointments', 'new_pts', 'sched', 'goal', 'prod'],
            ]);
    }

    public function test_appointment_details_data_supports_date_range(): void
    {
        $provider = OdProvider::create([
            'ProvNum' => 81,
            'LName' => 'Elias',
            'PName' => 'Kathy',
            'Abbr' => 'ELIAS',
        ]);

        OdAppointment::create([
            'AptNum' => 99994,
            'PatNum' => 1,
            'AptStatus' => 1,
            'Pattern' => '//',
            'Op' => 2,
            'ProvNum' => $provider->ProvNum,
            'AptDateTime' => '2026-07-10 10:00:00',
        ]);

        OdAppointment::create([
            'AptNum' => 99995,
            'PatNum' => 1,
            'AptStatus' => 1,
            'Pattern' => '//',
            'Op' => 2,
            'ProvNum' => $provider->ProvNum,
            'AptDateTime' => '2026-07-15 10:00:00',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.appointments-details-data', [
                'start' => '2026-07-01',
                'end' => '2026-07-20',
            ]));

        $response->assertOk()
            ->assertJsonFragment(['appointment_date' => 'Jul 10, 2026'])
            ->assertJsonFragment(['appointment_date' => 'Jul 15, 2026']);
    }

    public function test_appointment_capacity_data_supports_date_range(): void
    {
        $provider = OdProvider::create([
            'ProvNum' => 81,
            'LName' => 'Elias',
            'PName' => 'Kathy',
            'Abbr' => 'ELIAS',
        ]);

        OdAppointment::create([
            'AptNum' => 99996,
            'PatNum' => 1,
            'AptStatus' => 1,
            'Pattern' => '//',
            'Op' => 2,
            'ProvNum' => $provider->ProvNum,
            'AptDateTime' => '2026-07-10 10:00:00',
        ]);

        OdAppointment::create([
            'AptNum' => 99997,
            'PatNum' => 1,
            'AptStatus' => 1,
            'Pattern' => '//',
            'Op' => 2,
            'ProvNum' => $provider->ProvNum,
            'AptDateTime' => '2026-07-15 10:00:00',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.appointment-capacity-data', [
                'start' => '2026-07-01',
                'end' => '2026-07-20',
            ]));

        $response->assertOk()
            ->assertJsonFragment(['scheduled_appointments' => 2]);
    }
}
