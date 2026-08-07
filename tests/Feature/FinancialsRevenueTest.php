<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialsRevenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_financials_revenue_route_returns_revenue_metrics(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('financials.revenue', [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'gross_production',
            'net_production',
            'adjustments',
            'adjustment',
            'writeoffs',
            'collections',
            'collection',
            'adjustment_rate',
            'collection_rate',
        ]);
    }
}
