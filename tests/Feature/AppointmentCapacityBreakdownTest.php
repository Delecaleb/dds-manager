<?php

namespace Tests\Feature;

use App\Models\OdAppointment;
use App\Models\OdPatient;
use App\Models\OdProvider;
use App\Models\User;
use Tests\TestCase;

class AppointmentCapacityBreakdownTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $patient = OdPatient::create([
            'PatNum' => 101,
            'FName' => 'John',
            'LName' => 'Doe',
        ]);

        $provider = OdProvider::create([
            'ProvNum' => 81,
            'LName' => 'Elias',
            'PName' => 'Kathy',
            'Abbr' => 'ELIAS',
        ]);

        OdAppointment::create([
            'AptNum' => 88801,
            'PatNum' => $patient->PatNum,
            'ProvNum' => $provider->ProvNum,
            'AptStatus' => 1,
            'Pattern' => '///',
            'AptDateTime' => '2026-07-14 09:00:00',
            'IsNewPatient' => 1,
            'ProcDescript' => 'Emergency Exam D0140',
        ]);
    }

    public function test_capacity_breakdown_requires_authentication(): void
    {
        $response = $this->get(route('calendar.capacity-breakdown'));
        $response->assertRedirect(route('login'));
    }

    public function test_scheduled_appointments_breakdown_type(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.capacity-breakdown', [
                'date' => '2026-07-14',
                'type' => 'scheduled_appointments',
            ]));

        $response->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'patient',
                    'patient_id',
                    'date',
                    'provider',
                    'provider_id',
                ],
            ])
            ->assertJsonFragment([
                'patient_id' => '101',
                'date' => '2026-07-14',
                'provider_id' => '81',
            ]);
    }

    public function test_provider_count_breakdown_type(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.capacity-breakdown', [
                'date' => '2026-07-14',
                'type' => 'provider_count',
            ]));

        $response->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'provider',
                    'provider_name',
                    'provider_id',
                ],
            ])
            ->assertJsonFragment([
                'provider_id' => '81',
            ]);
    }

    public function test_booked_hours_breakdown_type(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.capacity-breakdown', [
                'date' => '2026-07-14',
                'type' => 'booked_hours',
            ]));

        $response->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'patient',
                    'patient_id',
                    'duration',
                    'provider',
                    'provider_id',
                ],
            ]);
    }

    public function test_avg_lead_all_breakdown_type(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.capacity-breakdown', [
                'date' => '2026-07-14',
                'type' => 'avg_lead_all',
            ]));

        $response->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'patient',
                    'patient_id',
                    'lead_time',
                    'provider',
                    'provider_id',
                ],
            ]);
    }

    public function test_avg_lead_new_breakdown_type(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.capacity-breakdown', [
                'date' => '2026-07-14',
                'type' => 'avg_lead_new',
            ]));

        $response->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'patient',
                    'patient_id',
                    'lead_time',
                    'provider',
                    'provider_id',
                ],
            ]);
    }

    public function test_avg_lead_emerg_breakdown_type(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('calendar.capacity-breakdown', [
                'date' => '2026-07-14',
                'type' => 'avg_lead_emerg',
            ]));

        $response->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'patient',
                    'patient_id',
                    'lead_time',
                    'provider',
                    'provider_id',
                ],
            ]);
    }
}
