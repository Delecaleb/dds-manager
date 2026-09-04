<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use App\Services\OpenDental\OperationsAnalyticsService;
use App\Services\OpenDental\ScheduleSnapshotService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScheduleSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private ScheduleSnapshotService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ScheduleSnapshotService::class);
    }

    public function test_should_date_be_locked_evaluates_correctly(): void
    {
        // 1. Past date must be locked
        $pastDate = Carbon::now('America/New_York')->subDays(2)->toDateString();
        $this->assertTrue($this->service->shouldDateBeLocked($pastDate));

        // 2. Future date must NOT be locked
        $futureDate = Carbon::now('America/New_York')->addDays(2)->toDateString();
        $this->assertFalse($this->service->shouldDateBeLocked($futureDate));

        // 3. Today evaluation: mock time before 8 AM EST and after 8 AM EST
        $todayEst = Carbon::now('America/New_York')->toDateString();

        Carbon::setTestNow(Carbon::parse("{$todayEst} 07:30:00", 'America/New_York'));
        $this->assertFalse($this->service->shouldDateBeLocked($todayEst));

        Carbon::setTestNow(Carbon::parse("{$todayEst} 08:00:00", 'America/New_York'));
        $this->assertTrue($this->service->shouldDateBeLocked($todayEst));

        Carbon::setTestNow(Carbon::parse("{$todayEst} 14:00:00", 'America/New_York'));
        $this->assertTrue($this->service->shouldDateBeLocked($todayEst));

        Carbon::setTestNow(); // reset
    }

    public function test_take_snapshot_captures_and_locks_past_schedule(): void
    {
        $office = Office::create(['name' => 'Main Clinic', 'is_active' => true]);
        $officeId = $office->id;
        session(['active_office_id' => $officeId]);
        $date = '2026-08-10';

        // Seed Appointments
        DB::table('od_appointments')->insert([
            [
                'office_id' => $officeId,
                'AptNum' => 1001,
                'PatNum' => 201,
                'ProvNum' => 10,
                'ClinicNum' => 1,
                'AptDateTime' => "{$date} 09:00:00",
                'AptStatus' => 1,
                'Pattern' => '//XXXX//', // 8 * 5 = 40 mins
                'IsNewPatient' => 1,
                'ProcDescript' => 'Comprehensive Exam',
            ],
            [
                'office_id' => $officeId,
                'AptNum' => 1002,
                'PatNum' => 202,
                'ProvNum' => 10,
                'ClinicNum' => 1,
                'AptDateTime' => "{$date} 10:00:00",
                'AptStatus' => 2,
                'Pattern' => '///XXXX///', // 10 * 5 = 50 mins
                'IsNewPatient' => 0,
                'ProcDescript' => 'Cleaning',
            ],
        ]);

        // Seed attached procedure fees
        DB::table('od_procedure_logs')->insert([
            [
                'office_id' => $officeId,
                'ProcNum' => 5001,
                'AptNum' => 1001,
                'PatNum' => 201,
                'ClinicNum' => 1,
                'ProcFee' => 250.00,
                'ProcStatus' => 1,
                'ProcDate' => $date,
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'office_id' => $officeId,
                'ProcNum' => 5002,
                'AptNum' => 1002,
                'PatNum' => 202,
                'ClinicNum' => 1,
                'ProcFee' => 150.00,
                'ProcStatus' => 2,
                'ProcDate' => $date,
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            // Unscheduled treatment plan procedure for scheduled patient 201
            [
                'office_id' => $officeId,
                'ProcNum' => 5003,
                'AptNum' => 0,
                'PatNum' => 201,
                'ClinicNum' => 1,
                'ProcFee' => 800.00,
                'ProcStatus' => 1, // TP
                'ProcDate' => $date,
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        // Seed Provider Schedule (8:00 AM to 12:00 PM = 240 mins)
        DB::table('od_schedules')->insert([
            'office_id' => $officeId,
            'SchedDate' => $date,
            'StartTime' => '08:00:00',
            'StopTime' => '12:00:00',
            'SchedType' => 1,
            'ClinicNum' => 1,
        ]);

        $result = $this->service->takeSnapshot($officeId, $date);

        $this->assertEquals('success', $result['status']);
        $this->assertTrue($result['locked']);

        // Verify Daily Summary
        $daily = DB::table('od_daily_schedule_snapshots')
            ->where('office_id', $officeId)
            ->where('snapshot_date', $date)
            ->where('clinic_num', 1)
            ->first();

        $this->assertNotNull($daily);
        $this->assertEquals(400.00, (float) $daily->sched_production);
        $this->assertEquals(2, $daily->sched_pts_visit);
        $this->assertEquals(1, $daily->sched_new_pts_visit);
        $this->assertEquals(800.00, (float) $daily->unscheduled_tx);
        // Sched mins: 240, Booked mins: 40 + 50 = 90. Open: (240 - 90)/60 = 2.50 hours
        $this->assertEquals(2.50, (float) $daily->open_appt_hours);
        $this->assertEquals(1, $daily->is_locked);

        // Verify Appointment Details
        $details = DB::table('od_appointment_schedule_snapshots')
            ->where('office_id', $officeId)
            ->where('snapshot_date', $date)
            ->get();

        $this->assertCount(2, $details);
        $apt1 = $details->where('apt_num', 1001)->first();
        $this->assertEquals(250.00, (float) $apt1->sched_production);
        $this->assertEquals(800.00, (float) $apt1->unscheduled_tx);
        $this->assertEquals(1, $apt1->is_new_patient);
        $this->assertEquals(1, $apt1->is_locked);
    }

    public function test_locked_snapshots_are_immutable(): void
    {
        $office = Office::create(['name' => 'Main Clinic', 'is_active' => true]);
        $officeId = $office->id;
        session(['active_office_id' => $officeId]);
        $date = '2026-08-10';

        // Seed 1 initial appointment
        DB::table('od_appointments')->insert([
            'office_id' => $officeId,
            'AptNum' => 2001,
            'PatNum' => 301,
            'ProvNum' => 10,
            'ClinicNum' => 0,
            'AptDateTime' => "{$date} 09:00:00",
            'AptStatus' => 1,
        ]);
        DB::table('od_procedure_logs')->insert([
            'office_id' => $officeId,
            'ProcNum' => 6001,
            'AptNum' => 2001,
            'PatNum' => 301,
            'ClinicNum' => 0,
            'ProcFee' => 300.00,
            'ProcStatus' => 1,
            'ProcDate' => $date,
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Take initial snapshot (locks it)
        $this->service->takeSnapshot($officeId, $date);

        $dailyBefore = DB::table('od_daily_schedule_snapshots')->where('office_id', $officeId)->where('snapshot_date', $date)->first();
        $this->assertEquals(300.00, (float) $dailyBefore->sched_production);

        // Add another appointment later
        DB::table('od_appointments')->insert([
            'office_id' => $officeId,
            'AptNum' => 2002,
            'PatNum' => 302,
            'ProvNum' => 10,
            'ClinicNum' => 0,
            'AptDateTime' => "{$date} 11:00:00",
            'AptStatus' => 1,
        ]);
        DB::table('od_procedure_logs')->insert([
            'office_id' => $officeId,
            'ProcNum' => 6002,
            'AptNum' => 2002,
            'PatNum' => 302,
            'ClinicNum' => 0,
            'ProcFee' => 500.00,
            'ProcStatus' => 1,
            'ProcDate' => $date,
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Attempt normal re-run: must be SKIPPED and preserved
        $reRunResult = $this->service->takeSnapshot($officeId, $date, false);
        $this->assertEquals('skipped', $reRunResult['status']);

        $dailyAfter = DB::table('od_daily_schedule_snapshots')->where('office_id', $officeId)->where('snapshot_date', $date)->first();
        $this->assertEquals(300.00, (float) $dailyAfter->sched_production);

        // Force overwrite: updates when force=true
        $forceResult = $this->service->takeSnapshot($officeId, $date, true);
        $this->assertEquals('success', $forceResult['status']);

        $dailyForced = DB::table('od_daily_schedule_snapshots')->where('office_id', $officeId)->where('snapshot_date', $date)->first();
        $this->assertEquals(800.00, (float) $dailyForced->sched_production);
    }

    public function test_future_snapshots_update_dynamically(): void
    {
        $office = Office::create(['name' => 'Main Clinic', 'is_active' => true]);
        $officeId = $office->id;
        session(['active_office_id' => $officeId]);
        $futureDate = Carbon::now('America/New_York')->addDays(5)->toDateString();

        // Seed 1 appointment for future date
        DB::table('od_appointments')->insert([
            'office_id' => $officeId,
            'AptNum' => 3001,
            'PatNum' => 401,
            'ClinicNum' => 0,
            'AptDateTime' => "{$futureDate} 09:00:00",
            'AptStatus' => 1,
        ]);
        DB::table('od_procedure_logs')->insert([
            'office_id' => $officeId,
            'ProcNum' => 7001,
            'AptNum' => 3001,
            'PatNum' => 401,
            'ClinicNum' => 0,
            'ProcFee' => 200.00,
            'ProcStatus' => 1,
            'ProcDate' => $futureDate,
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Take snapshot for future date
        $res = $this->service->takeSnapshot($officeId, $futureDate);
        $this->assertEquals('success', $res['status']);
        $this->assertFalse($res['locked']);

        $daily = DB::table('od_daily_schedule_snapshots')->where('office_id', $officeId)->where('snapshot_date', $futureDate)->first();
        $this->assertEquals(200.00, (float) $daily->sched_production);
        $this->assertEquals(0, $daily->is_locked);

        // Patient books an additional procedure on the future appointment
        DB::table('od_procedure_logs')->insert([
            'office_id' => $officeId,
            'ProcNum' => 7002,
            'AptNum' => 3001,
            'PatNum' => 401,
            'ClinicNum' => 0,
            'ProcFee' => 350.00,
            'ProcStatus' => 1,
            'ProcDate' => $futureDate,
            'MedicalCode' => '',
            'ToothNum' => '',
        ]);

        // Re-run normal snapshot: future date updates dynamically without needing force
        $res2 = $this->service->takeSnapshot($officeId, $futureDate, false);
        $this->assertEquals('success', $res2['status']);

        $dailyUpdated = DB::table('od_daily_schedule_snapshots')->where('office_id', $officeId)->where('snapshot_date', $futureDate)->first();
        $this->assertEquals(550.00, (float) $dailyUpdated->sched_production);
    }

    public function test_operations_performance_tab_uses_snapshots(): void
    {
        $office = Office::create(['name' => 'Main Clinic', 'is_active' => true]);
        $officeId = $office->id;
        session(['active_office_id' => $officeId]);

        $date = '2026-08-15';

        // Pre-insert locked snapshot
        DB::table('od_daily_schedule_snapshots')->insert([
            'office_id' => $officeId,
            'clinic_num' => 0,
            'snapshot_date' => $date,
            'sched_production' => 1500.00,
            'sched_pts_visit' => 8,
            'sched_new_pts_visit' => 2,
            'open_appt_hours' => 3.5,
            'unscheduled_tx' => 2200.00,
            'is_locked' => true,
            'snapshot_taken_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $analytics = app(OperationsAnalyticsService::class);
        $perf = $analytics->performance($date, $date);

        $this->assertNotEmpty($perf['rows']);
        $row = $perf['rows'][0];

        $this->assertEquals(1500.00, $row['sched_production']);
        $this->assertEquals(8, $row['sched_pts_visit']);
        $this->assertEquals(2, $row['sched_new_pts_visit']);
        $this->assertEquals(3.5, $row['open_appt_hours']);
        $this->assertEquals(2200.00, $row['unscheduled_tx']);
    }

    public function test_operations_drilldown_returns_snapshot_appointments(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $office = Office::create(['name' => 'Main Clinic', 'is_active' => true]);
        $officeId = $office->id;
        session(['active_office_id' => $officeId]);

        $date = '2026-08-15';

        // Seed Patient & Provider
        DB::table('od_patients')->insert([
            'office_id' => $officeId,
            'PatNum' => 801,
            'FName' => 'Jane',
            'LName' => 'Doe',
        ]);
        DB::table('od_providers')->insert([
            'office_id' => $officeId,
            'ProvNum' => 55,
            'PName' => 'Bob',
            'LName' => 'Smith',
            'Abbr' => 'BS',
        ]);

        // Insert snapshot appointment detail
        DB::table('od_appointment_schedule_snapshots')->insert([
            'office_id' => $officeId,
            'clinic_num' => 0,
            'snapshot_date' => $date,
            'apt_num' => 9001,
            'pat_num' => 801,
            'prov_num' => 55,
            'apt_date_time' => "{$date} 10:00:00",
            'apt_status' => 1,
            'is_new_patient' => true,
            'proc_descript' => 'Root Canal',
            'sched_production' => 1200.00,
            'unscheduled_tx' => 450.00,
            'is_locked' => true,
            'snapshot_taken_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test drilldown on sched_production
        $response = $this->getJson(route('operations.drilldown', [
            'metric' => 'sched_production',
            'start_date' => $date,
            'end_date' => $date,
            'office_id' => $officeId,
        ]));

        $response->assertOk();
        $response->assertSee('Doe, Jane');
        $response->assertSee('1,200.00');

        // Test drilldown on unscheduled_tx
        $responseTx = $this->getJson(route('operations.drilldown', [
            'metric' => 'unscheduled_tx',
            'start_date' => $date,
            'end_date' => $date,
            'office_id' => $officeId,
        ]));

        $responseTx->assertOk();
        $responseTx->assertSee('Doe, Jane');
        $responseTx->assertSee('450.00');
    }

    public function test_artisan_command_snapshot_daily_schedule(): void
    {
        $office = Office::create(['name' => 'Main Clinic', 'is_active' => true]);

        $this->artisan('snapshot:daily-schedule', [
            '--office-id' => $office->id,
            '--date' => '2026-08-01',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('od_daily_schedule_snapshots', [
            'office_id' => $office->id,
            'snapshot_date' => '2026-08-01',
            'is_locked' => 1,
        ]);
    }
}
