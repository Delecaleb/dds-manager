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

    public function test_calendar_stats_production_supports_date_range_for_week_and_month(): void
    {
        // Day 1 (July 14): Completed procedure 200.00 gross
        OdProcedureLog::create([
            'ProcNum' => 8882,
            'PatNum' => 1,
            'ProcFee' => '200.00',
            'ProcStatus' => 'C',
            'ProcDate' => '2026-07-14 00:00:00',
        ]);

        // Day 2 (July 16): Completed procedure 350.00 gross
        OdProcedureLog::create([
            'ProcNum' => 8883,
            'PatNum' => 1,
            'ProcFee' => '350.00',
            'ProcStatus' => 'C',
            'ProcDate' => '2026-07-16 00:00:00',
        ]);

        // Day 2 Adjustment: -50.00
        OdAdjustment::create([
            'AdjNum' => 8882,
            'PatNum' => 1,
            'AdjAmt' => '-50.00',
            'AdjDate' => '2026-07-16 00:00:00',
        ]);

        // Week range: July 13 to July 19 -> Net production = (200 + 350 - 50) = 500.00
        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.stats', [
                'start' => '2026-07-13',
                'end' => '2026-07-19',
            ]));

        $response->assertOk()
            ->assertJsonFragment([
                'production' => 500.0,
                'scheduled_production' => 550.0,
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

    public function test_calendar_scheduled_production_breakdown_supports_date_range(): void
    {
        $provider = OdProvider::create([
            'ProvNum' => 82,
            'LName' => 'Smith',
            'PName' => 'John',
            'Abbr' => 'SMITH',
        ]);

        $apt1 = OdAppointment::create([
            'AptNum' => 99994,
            'PatNum' => 1,
            'AptStatus' => 1,
            'Pattern' => '//',
            'Op' => 1,
            'ProvNum' => $provider->ProvNum,
            'AptDateTime' => '2026-07-14 10:00:00',
        ]);

        OdProcedureLog::create([
            'ProcNum' => 8884,
            'PatNum' => 1,
            'AptNum' => $apt1->AptNum,
            'ProcFee' => '150.00',
            'ProcStatus' => '1',
            'ProcDate' => '2026-07-14 00:00:00',
        ]);

        $apt2 = OdAppointment::create([
            'AptNum' => 99995,
            'PatNum' => 2,
            'AptStatus' => 1,
            'Pattern' => '//',
            'Op' => 1,
            'ProvNum' => $provider->ProvNum,
            'AptDateTime' => '2026-07-16 11:00:00',
        ]);

        OdProcedureLog::create([
            'ProcNum' => 8885,
            'PatNum' => 2,
            'AptNum' => $apt2->AptNum,
            'ProcFee' => '250.00',
            'ProcStatus' => '1',
            'ProcDate' => '2026-07-16 00:00:00',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.scheduled-production-breakdown', [
                'start' => '2026-07-13',
                'end' => '2026-07-19',
            ]));

        $response->assertOk()
            ->assertJsonFragment([
                'total_scheduled' => 400.0,
                'appointment_count' => 2,
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
            ->assertJsonFragment(['appointment_date' => '2026-07-10'])
            ->assertJsonFragment(['appointment_date' => '2026-07-15']);
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

    public function test_appointment_details_data_calculates_5min_duration_and_supports_sorting(): void
    {
        $provider = OdProvider::create([
            'ProvNum' => 81,
            'LName' => 'Elias',
            'PName' => 'Kathy',
            'Abbr' => 'ELIAS',
        ]);

        // Pattern of length 12 -> 12 * 5 = 60 minutes ("60.00")
        OdAppointment::create([
            'AptNum' => 99998,
            'PatNum' => 1,
            'AptStatus' => 1,
            'Pattern' => 'XXXXXXXXXXXX',
            'Op' => 1,
            'ProvNum' => $provider->ProvNum,
            'AptDateTime' => '2026-07-14 10:00:00',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.appointments-details-data', [
                'draw' => 1,
                'start' => '2026-07-14',
                'end' => '2026-07-14',
                'columns' => [
                    ['data' => 'location', 'name' => 'location'],
                    ['data' => 'patient_name', 'name' => 'patient_name'],
                    ['data' => 'appointment_date', 'name' => 'appointment_date'],
                    ['data' => 'appointment_time', 'name' => 'appointment_time'],
                    ['data' => 'appointment_duration', 'name' => 'appointment_duration'],
                ],
                'order' => [
                    ['column' => 4, 'dir' => 'desc'],
                ],
            ]));

        $response->assertOk()
            ->assertJsonFragment(['appointment_duration' => '60.00']);
    }

    public function test_appointment_details_data_filters_by_provider_and_status(): void
    {
        $prov1 = OdProvider::create([
            'ProvNum' => 88,
            'LName' => 'DocA',
            'PName' => 'Alpha',
            'Abbr' => 'DOCA',
        ]);
        $prov2 = OdProvider::create([
            'ProvNum' => 89,
            'LName' => 'DocB',
            'PName' => 'Beta',
            'Abbr' => 'DOCB',
        ]);

        OdAppointment::create([
            'AptNum' => 99991,
            'PatNum' => 1,
            'AptStatus' => 1, // Scheduled
            'ProvNum' => $prov1->ProvNum,
            'AptDateTime' => '2026-07-14 10:00:00',
        ]);

        OdAppointment::create([
            'AptNum' => 99992,
            'PatNum' => 2,
            'AptStatus' => 2, // Completed
            'ProvNum' => $prov2->ProvNum,
            'AptDateTime' => '2026-07-14 11:00:00',
        ]);

        // Filter by provider 88
        $responseProv = $this->actingAs($this->user)
            ->getJson(route('calendar.appointments-details-data', [
                'start' => '2026-07-14',
                'end' => '2026-07-14',
                'provider_id' => 88,
            ]));

        $responseProv->assertOk()
            ->assertJsonFragment(['provider_name' => 'DocA, Alpha'])
            ->assertJsonMissing(['provider_name' => 'DocB, Beta']);

        // Filter by status 2 (Completed)
        $responseStatus = $this->actingAs($this->user)
            ->getJson(route('calendar.appointments-details-data', [
                'start' => '2026-07-14',
                'end' => '2026-07-14',
                'status' => 2,
            ]));

        $responseStatus->assertOk()
            ->assertJsonFragment(['provider_name' => 'DocB, Beta'])
            ->assertJsonMissing(['provider_name' => 'DocA, Alpha']);
    }

    public function test_appointment_details_data_maps_operatory_and_confirmation_status(): void
    {
        DB::table('od_definitions')->insert([
            'DefNum' => 201,
            'Category' => 2,
            'ItemName' => 'CheckOut',
        ]);

        OdAppointment::create([
            'AptNum' => 99999,
            'PatNum' => 50,
            'AptStatus' => 2,
            'Op' => 6, // Unassigned 6
            'Confirmed' => 201,
            'AptDateTime' => '2026-08-25 14:00:00',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.appointments-details-data', [
                'start' => '2026-08-25',
                'end' => '2026-08-25',
            ]));

        $response->assertOk()
            ->assertJsonFragment(['operatory_name' => 'Unassigned 6'])
            ->assertJsonFragment(['confirmation_status' => 'CheckOut'])
            ->assertJsonFragment(['appointment_time' => '14:00 PM'])
            ->assertJsonFragment(['appointment_date' => '2026-08-25']);
    }

    public function test_appointment_details_data_resolves_insurance_procedures_and_metrics(): void
    {
        $carrier = OdCarrier::create([
            'CarrierNum' => 77,
            'CarrierName' => 'Delta Dental of MI',
        ]);

        $insPlan = OdInsplan::create([
            'PlanNum' => 301,
            'CarrierNum' => $carrier->CarrierNum,
        ]);

        $patient = OdPatient::create([
            'PatNum' => 60,
            'FName' => 'John',
            'LName' => 'Doe',
            'WirelessPhone' => '3135551234',
            'Email' => 'john.doe@example.com',
            'Birthdate' => '1990-05-15',
        ]);

        $proc = OdProcedure::create([
            'CodeNum' => 101,
            'ProcCode' => 'D8670',
        ]);

        // Prior completed procedure for last visit date
        OdProcedureLog::create([
            'ProcNum' => 5001,
            'PatNum' => 60,
            'ProcDate' => '2026-06-12',
            'ProcStatus' => '2',
            'ProcFee' => 150,
        ]);

        // Unscheduled treatment procedure
        OdProcedureLog::create([
            'ProcNum' => 5002,
            'PatNum' => 60,
            'ProcDate' => '2026-08-01',
            'ProcStatus' => '1', // Treatment planned
            'ProcFee' => 118,
            'AptNum' => 0,
        ]);

        // Appointment
        $apt = OdAppointment::create([
            'AptNum' => 99990,
            'PatNum' => 60,
            'AptStatus' => 2, // Completed
            'Op' => 3, // DR-3
            'InsPlan1' => $insPlan->PlanNum,
            'IsNewPatient' => 0,
            'AptDateTime' => '2026-08-25 13:00:00',
        ]);

        // Procedure completed on appointment
        OdProcedureLog::create([
            'ProcNum' => 5003,
            'PatNum' => 60,
            'AptNum' => $apt->AptNum,
            'CodeNum' => $proc->CodeNum,
            'ProcDate' => '2026-08-25',
            'ProcStatus' => '2',
            'ProcFee' => 5000,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.appointments-details-data', [
                'start' => '2026-08-25',
                'end' => '2026-08-25',
            ]));

        $response->assertOk()
            ->assertJsonFragment(['patient_name' => 'John Doe'])
            ->assertJsonFragment(['operatory_name' => 'DR-3'])
            ->assertJsonFragment(['patient_type' => 'Existing'])
            ->assertJsonFragment(['patient_phone' => '(313)-555-1234'])
            ->assertJsonFragment(['email_address' => 'john.doe@example.com'])
            ->assertJsonFragment(['procedure_codes' => 'D8670'])
            ->assertJsonFragment(['production' => '$ 5,000.00'])
            ->assertJsonFragment(['primary_insurance' => 'Delta Dental of MI'])
            ->assertJsonFragment(['secondary_insurance' => 'N/A'])
            ->assertJsonFragment(['referral_source' => 'No Source Listed'])
            ->assertJsonFragment(['unscheduled_tx' => '$ 118.00'])
            ->assertJsonFragment(['last_visit_date' => '2026-08-25']);
    }
}
