<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class DepositSlipTest extends TestCase
{
    public function test_deposit_slip_page_loads_successfully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('deposits.index'));

        $response->assertStatus(200);
        $response->assertSee('Deposit Slip');
        $response->assertSee('detailContainer');
        $response->assertSee('data-sort="office"', false);
        $response->assertSee('data-sort="patient_name"', false);
        $response->assertSee('data-sort="amount"', false);
    }

    public function test_deposit_slip_data_endpoint_returns_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('deposits.data', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'deposits',
            'details',
            'summary' => [
                'total_amount',
            ],
        ]);
    }
}
