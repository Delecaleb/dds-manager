<?php

namespace Tests\Feature;

use App\Models\OdAppointment;
use App\Models\OdPatient;
use App\Models\OdProcedure;
use App\Models\OdProcedureLog;
use App\Models\OdProvider;
use App\Models\Office;
use App\Models\PaySplit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiOfficeReportingScopingTest extends TestCase
{
    use RefreshDatabase;

    private Office $officeA;

    private Office $officeB;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->officeA = Office::create(['name' => 'Downtown Dental', 'is_active' => true]);
        $this->officeB = Office::create(['name' => 'Uptown Dental', 'is_active' => true]);

        $this->user = User::factory()->superAdmin()->create();

        // Seed procedures (IsHygiene 'false' for doctors, 'true' for hygiene)
        OdProcedure::create(['office_id' => $this->officeA->id, 'CodeNum' => 101, 'ProcCode' => 'D0120', 'Descript' => 'Periodic Exam', 'IsHygiene' => 'false']);
        OdProcedure::create(['office_id' => $this->officeB->id, 'CodeNum' => 101, 'ProcCode' => 'D0120', 'Descript' => 'Periodic Exam', 'IsHygiene' => 'false']);

        // Seed providers
        OdProvider::create(['office_id' => $this->officeA->id, 'ProvNum' => 1, 'LName' => 'Smith', 'FName' => 'John', 'Abbr' => 'JS', 'IsHidden' => 'false']);
        OdProvider::create(['office_id' => $this->officeB->id, 'ProvNum' => 2, 'LName' => 'Doe', 'FName' => 'Jane', 'Abbr' => 'JD', 'IsHidden' => 'false']);

        // Seed patients
        OdPatient::create(['office_id' => $this->officeA->id, 'PatNum' => 1001, 'FName' => 'Alice', 'LName' => 'A', 'PatStatus' => 'Patient', 'PriProv' => 1]);
        OdPatient::create(['office_id' => $this->officeB->id, 'PatNum' => 2001, 'FName' => 'Bob', 'LName' => 'B', 'PatStatus' => 'Patient', 'PriProv' => 2]);

        // Seed procedure logs for Office A ($500 gross)
        OdProcedureLog::create([
            'office_id' => $this->officeA->id,
            'ProcNum' => 5001,
            'PatNum' => 1001,
            'CodeNum' => 101,
            'ProvNum' => 1,
            'ClinicNum' => 0,
            'ProcDate' => '2026-08-15',
            'ProcFee' => 500.00,
            'ProcStatus' => 2, // Complete
        ]);

        // Seed procedure logs for Office B ($1200 gross)
        OdProcedureLog::create([
            'office_id' => $this->officeB->id,
            'ProcNum' => 6001,
            'PatNum' => 2001,
            'CodeNum' => 101,
            'ProvNum' => 2,
            'ClinicNum' => 0,
            'ProcDate' => '2026-08-15',
            'ProcFee' => 1200.00,
            'ProcStatus' => 2, // Complete
        ]);

        // Seed pay splits for collections
        PaySplit::create([
            'office_id' => $this->officeA->id,
            'SplitNum' => 7001,
            'PatNum' => 1001,
            'ProvNum' => 1,
            'ClinicNum' => 0,
            'DatePay' => '2026-08-15',
            'SplitAmt' => 300.00,
        ]);

        PaySplit::create([
            'office_id' => $this->officeB->id,
            'SplitNum' => 8001,
            'PatNum' => 2001,
            'ProvNum' => 2,
            'ClinicNum' => 0,
            'DatePay' => '2026-08-15',
            'SplitAmt' => 900.00,
        ]);

        // Seed appointments
        OdAppointment::create([
            'office_id' => $this->officeA->id,
            'AptNum' => 9001,
            'PatNum' => 1001,
            'ProvNum' => 1,
            'ClinicNum' => 0,
            'AptDateTime' => '2026-08-15 09:00:00',
            'AptStatus' => 1,
            'Pattern' => '///',
        ]);

        OdAppointment::create([
            'office_id' => $this->officeB->id,
            'AptNum' => 9002,
            'PatNum' => 2001,
            'ProvNum' => 2,
            'ClinicNum' => 0,
            'AptDateTime' => '2026-08-15 10:00:00',
            'AptStatus' => 1,
            'Pattern' => '//////',
        ]);
    }

    public function test_dashboard_data_strictly_scopes_by_active_office(): void
    {
        // Office A
        $responseA = $this->actingAs($this->user)
            ->withSession(['active_office_id' => $this->officeA->id])
            ->getJson('/dashboard/data?start_date=2026-08-01&end_date=2026-08-31');

        $responseA->assertOk();
        $this->assertEquals(500.00, (float) $responseA->json('gross_production'));
        $this->assertEquals(300.00, (float) $responseA->json('collections'));

        // Office B
        $responseB = $this->actingAs($this->user)
            ->withSession(['active_office_id' => $this->officeB->id])
            ->getJson('/dashboard/data?start_date=2026-08-01&end_date=2026-08-31');

        $responseB->assertOk();
        $this->assertEquals(1200.00, (float) $responseB->json('gross_production'));
        $this->assertEquals(900.00, (float) $responseB->json('collections'));
    }

    public function test_dashboard_location_stats_strictly_scopes_by_active_office(): void
    {
        // Office A
        $responseA = $this->actingAs($this->user)
            ->withSession(['active_office_id' => $this->officeA->id])
            ->getJson('/dashboard/location-stats?start_date=2026-08-01&end_date=2026-08-31');

        $responseA->assertOk();
        $dataA = $responseA->json();
        $this->assertCount(1, $dataA);
        $this->assertEquals('Downtown Dental', $dataA[0]['location']);
        $this->assertEquals(500.00, (float) $dataA[0]['total_production']);

        // Office B
        $responseB = $this->actingAs($this->user)
            ->withSession(['active_office_id' => $this->officeB->id])
            ->getJson('/dashboard/location-stats?start_date=2026-08-01&end_date=2026-08-31');

        $responseB->assertOk();
        $dataB = $responseB->json();
        $this->assertCount(1, $dataB);
        $this->assertEquals('Uptown Dental', $dataB[0]['location']);
        $this->assertEquals(1200.00, (float) $dataB[0]['total_production']);
    }

    public function test_financials_revenue_and_scorecards_strictly_scope_by_active_office(): void
    {
        // Office A Financials
        $responseA = $this->actingAs($this->user)
            ->withSession(['active_office_id' => $this->officeA->id])
            ->getJson('/financials/revenue?start_date=2026-08-01&end_date=2026-08-31');

        $responseA->assertOk();
        $this->assertEquals(500.00, (float) $responseA->json('gross_production'));

        // Office B Financials
        $responseB = $this->actingAs($this->user)
            ->withSession(['active_office_id' => $this->officeB->id])
            ->getJson('/financials/revenue?start_date=2026-08-01&end_date=2026-08-31');

        $responseB->assertOk();
        $this->assertEquals(1200.00, (float) $responseB->json('gross_production'));
    }

    public function test_calendar_stats_strictly_scopes_by_active_office(): void
    {
        // Office A Calendar
        $responseA = $this->actingAs($this->user)
            ->withSession(['active_office_id' => $this->officeA->id])
            ->getJson('/calendar/stats?start=2026-08-01&end=2026-08-31');

        $responseA->assertOk();
        $this->assertEquals(500.00, (float) $responseA->json('production'));

        // Office B Calendar
        $responseB = $this->actingAs($this->user)
            ->withSession(['active_office_id' => $this->officeB->id])
            ->getJson('/calendar/stats?start=2026-08-01&end=2026-08-31');

        $responseB->assertOk();
        $this->assertEquals(1200.00, (float) $responseB->json('production'));
    }

    public function test_kpis_doctor_strictly_scopes_by_active_office(): void
    {
        // Office A Doctor KPI
        $responseA = $this->actingAs($this->user)
            ->withSession(['active_office_id' => $this->officeA->id])
            ->getJson('/kpis/doctor?start_date=2026-08-01&end_date=2026-08-31');

        $responseA->assertOk();
        $this->assertEquals(500.00, (float) $responseA->json('total_production'));

        // Office B Doctor KPI
        $responseB = $this->actingAs($this->user)
            ->withSession(['active_office_id' => $this->officeB->id])
            ->getJson('/kpis/doctor?start_date=2026-08-01&end_date=2026-08-31');

        $responseB->assertOk();
        $this->assertEquals(1200.00, (float) $responseB->json('total_production'));
    }

    public function test_provider_portal_strictly_scopes_by_active_office(): void
    {
        // Office A Providers
        $responseA = $this->actingAs($this->user)
            ->withSession(['active_office_id' => $this->officeA->id])
            ->getJson('/provider-portal/providers?start_date=2026-08-01&end_date=2026-08-31');

        $responseA->assertOk();
        $providersA = $responseA->json();
        $this->assertCount(1, $providersA);
        $this->assertEquals(1, $providersA[0]['id']);

        // Office B Providers
        $responseB = $this->actingAs($this->user)
            ->withSession(['active_office_id' => $this->officeB->id])
            ->getJson('/provider-portal/providers?start_date=2026-08-01&end_date=2026-08-31');

        $responseB->assertOk();
        $providersB = $responseB->json();
        $this->assertCount(1, $providersB);
        $this->assertEquals(2, $providersB[0]['id']);
    }
}
