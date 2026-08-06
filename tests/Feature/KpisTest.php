<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class KpisTest extends TestCase
{
    public function test_kpis_index_page_requires_auth(): void
    {
        $response = $this->get('/kpis');
        $response->assertRedirect('/login');
    }

    public function test_kpis_index_page_is_accessible_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/kpis');
        $response->assertStatus(200);
        $response->assertSee('Hygiene');
    }

    public function test_hygiene_kpi_endpoint_returns_all_22_metrics(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/kpis/hygiene?start_date=2026-01-01&end_date=2026-07-01');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'perio_pct',
            'fluoride_per_day',
            'avg_prod_per_day',
            'avg_prod_per_prov_day',
            'prod_per_visit',
            'fmx_per_day',
            'srp_per_day',
            'visits_per_day',
            'reappt',
            'perio_reappt',
            'adult_retention_12m',
            'adult_retention_6m',
            'child_retention_12m',
            'child_retention_6m',
            'sealants',
            'whitening',
            'antimicrobial',
            'prod_per_proc',
            'visits_with_tx_pct',
            'tx_plans_per_day',
            'avg_prod_per_hour',
            'case_acceptance',
        ]);
    }

    public function test_hygiene_providers_endpoint_returns_data_structure(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/kpis/hygiene-providers?start_date=2026-01-01&end_date=2026-07-01');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'providers',
            'avg',
            'total',
        ]);
    }

    public function test_doctor_kpi_endpoint_returns_all_16_metrics(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/kpis/doctor?start_date=2026-01-01&end_date=2026-07-01');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'case_acceptance_same_day',
            'case_acceptance_rate',
            'new_pt_tx_dollars',
            'existing_pt_tx_dollars',
            'avg_apt_time_mins',
            'avg_prod_per_hour',
            'avg_prod_per_apt',
            'same_day_tx_per_new_pt',
            'avg_prod_per_prov_day',
            'avg_tx_per_existing_pt',
            'avg_tx_per_new_pt',
            'pct_new_pt_with_tx',
            'pct_existing_pt_with_tx',
            'reappt',
            'prod_per_exam',
            'total_production',
        ]);
    }

    public function test_office_kpi_endpoint_returns_all_11_metrics(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/kpis/office?start_date=2026-01-01&end_date=2026-07-01');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'patient_retention',
            'tx_plans_per_day',
            'co_pay_collection',
            'unscheduled_tx',
            'new_pt_fmx_pct',
            'no_show_rate',
            'reactivation_list',
            'patient_attrition',
            'patient_growth',
            'active_patients',
            'active_in_recare_pct',
        ]);
    }
}
