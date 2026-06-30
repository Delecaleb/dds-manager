<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OpenDental\AppointmentService;
use App\Services\OpenDental\PatientService;
use App\Services\OpenDental\ProcedureService;
use App\Services\OpenDental\TreatmentPlanService;
use App\Services\OpenDental\PaymentService;
use App\Services\OpenDental\ProviderService;
use Tests\TestCase;

class PatientShowTest extends TestCase
{
    public function test_patient_show_returns_full_dataset(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $basePatient = [
            'PatNum' => 1,
            'FName' => 'Ada',
            'LName' => 'Lovelace',
            'WirelessPhone' => '5551234',
            'WkPhone' => '5559876',
            'HmPhone' => '5550001',
            'Email' => 'ada@example.com',
            'Birthdate' => '2001-01-01',
            'Address' => '1 Main',
            'Address2' => '',
            'City' => 'Detroit',
            'Zip' => '48201',
            'State' => 'MI',
            'Gender' => 1,
            'PatStatus' => 0,
            'Guarantor' => 1,
            'BalTotal' => 150.00,
            'InsEst' => 50.00,
            'Bal_0_30' => 100.00,
            'Bal_31_60' => 0.00,
            'Bal_61_90' => 0.00,
            'Bal_Over90' => 0.00,
            'PatNote' => 'Note test',
            'DateRecallDue' => '2026-08-01',
            'Employer' => 'Acme Corp',
        ];

        $patientsMock = $this->createMock(PatientService::class);
        $patientsMock->method('find')->willReturn($basePatient);
        $patientsMock->method('all')->willReturn([$basePatient]);
        app()->instance(PatientService::class, $patientsMock);

        $appointmentsMock = $this->createMock(AppointmentService::class);
        $appointmentsMock->method('all')->willReturn([
            [
                'PatNum' => 1,
                'AptDateTime' => '2026-06-29 20:00:00',
                'AptStatus' => 2,
                'ProcDescript' => 'Routine Cleaning',
            ]
        ]);
        app()->instance(AppointmentService::class, $appointmentsMock);

        $proceduresMock = $this->createMock(ProcedureService::class);
        $proceduresMock->method('all')->willReturn([
            [
                'PatNum' => 1,
                'ProcCode' => 'D0120',
                'ProcDescript' => 'Periodic Oral Eval',
                'ToothNum' => '',
                'Surf' => '',
                'ProcFee' => 75.00,
                'ProvNum' => 2,
                'ProcStatus' => 'C',
                'ProcDate' => '2026-06-28',
            ]
        ]);
        app()->instance(ProcedureService::class, $proceduresMock);

        $treatmentsMock = $this->createMock(TreatmentPlanService::class);
        $treatmentsMock->method('all')->willReturn([]);
        app()->instance(TreatmentPlanService::class, $treatmentsMock);

        $paymentsMock = $this->createMock(PaymentService::class);
        $paymentsMock->method('all')->willReturn([
            [
                'PatNum' => 1,
                'PayAmt' => 50.00,
                'PayDate' => '2026-06-29',
            ]
        ]);
        app()->instance(PaymentService::class, $paymentsMock);

        $providersMock = $this->createMock(ProviderService::class);
        $providersMock->method('all')->willReturn([
            [
                'ProvNum' => 2,
                'LName' => 'Smith',
            ]
        ]);
        app()->instance(ProviderService::class, $providersMock);

        $response = $this->getJson(route('patients.show', ['id' => 1]));

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'age',
                'gender',
                'birthdate',
                'status',
                'mobile_phone',
                'work_phone',
                'home_phone',
                'email',
                'address',
                'city',
                'state',
                'zip',
                'overview',
                'family',
                'ledger',
                'txplans',
                'ar',
                'employer',
                'notes',
            ]);

        $response->assertJson([
            'name' => 'Lovelace, Ada',
            'email' => 'ada@example.com',
            'employer' => 'Acme Corp',
            'notes' => 'Note test',
        ]);
    }
}
