<?php

namespace Tests\Feature;

use App\Models\User;
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
}
