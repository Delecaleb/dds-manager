<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FinancialsScoreCardsTest extends TestCase
{
    public function test_scorecards_production_tab_returns_ok_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('financials.score-cards', [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'tab' => 'production',
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'kpis' => [
                'total_count',
                'unique_by_pricing',
                'total_production',
            ],
            'chart_counts',
            'chart_services',
            'rows',
            'providers',
        ]);
    }

    public function test_scorecards_collection_tab_returns_ok_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('financials.score-cards', [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'tab' => 'collection',
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'kpis' => [
                'total_count',
                'total_payments',
            ],
            'chart_counts',
            'chart_payments',
            'rows',
            'providers',
        ]);
    }

    public function test_unique_services_by_pricing_counts_distinct_procedure_services(): void
    {
        $user = User::factory()->create();

        DB::table('od_providers')->insert([
            ['ProvNum' => 1, 'Abbr' => 'Dr A', 'LName' => 'Smith', 'PName' => 'John', 'IsHidden' => 0],
            ['ProvNum' => 2, 'Abbr' => 'Dr B', 'LName' => 'Jones', 'PName' => 'Mary', 'IsHidden' => 0],
        ]);

        DB::table('od_procedures')->insert([
            ['CodeNum' => 1, 'ProcCode' => 'D0120', 'Descript' => 'Periodic Exam'],
            ['CodeNum' => 2, 'ProcCode' => 'D1110', 'Descript' => 'Prophylaxis Adult'],
        ]);

        DB::table('od_procedure_logs')->insert([
            [
                'ProcNum' => 101,
                'PatNum' => 1,
                'ProvNum' => 1,
                'CodeNum' => 1,
                'ProcFee' => 50.00,
                'ProcStatus' => 2,
                'ProcDate' => '2026-07-05',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 102,
                'PatNum' => 2,
                'ProvNum' => 2,
                'CodeNum' => 1,
                'ProcFee' => 50.00,
                'ProcStatus' => 2,
                'ProcDate' => '2026-07-06',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
            [
                'ProcNum' => 103,
                'PatNum' => 1,
                'ProvNum' => 1,
                'CodeNum' => 2,
                'ProcFee' => 100.00,
                'ProcStatus' => 2,
                'ProcDate' => '2026-07-10',
                'MedicalCode' => '',
                'ToothNum' => '',
            ],
        ]);

        $response = $this->actingAs($user)->getJson(route('financials.score-cards', [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'tab' => 'production',
        ]));

        $response->assertOk();
        $data = $response->json();

        // 3 procedure instances across 2 distinct priced service combinations (Prov 1 D0120 $50, Prov 2 D0120 $50, Prov 1 D1110 $100)
        $this->assertEquals(3, $data['kpis']['total_count']);
        $this->assertEquals(3, $data['kpis']['unique_by_pricing']);
    }
}
