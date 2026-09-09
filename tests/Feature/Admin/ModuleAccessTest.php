<?php

namespace Tests\Feature\Admin;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an active office so layouts and controllers have office context
        Office::create([
            'name' => 'Main Clinic',
            'is_active' => true,
        ]);
    }

    public function test_super_admin_has_unrestricted_access_to_all_modules(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $modules = [
            route('dashboard'),
            route('patients.index'),
            route('calendar.index'),
            route('financials.index'),
            route('kpis.index'),
            route('aging.index'),
            route('front-office.index'),
        ];

        foreach ($modules as $url) {
            $response = $this->actingAs($superAdmin)->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_user_with_specific_module_access_can_access_that_module(): void
    {
        $user = User::factory()->staff()->create();
        $user->syncModules(['patients', 'calendar']);

        $patientsResponse = $this->actingAs($user)->get(route('patients.index'));
        $patientsResponse->assertStatus(200);

        $calendarResponse = $this->actingAs($user)->get(route('calendar.index'));
        $calendarResponse->assertStatus(200);
    }

    public function test_user_without_module_access_receives_forbidden(): void
    {
        $user = User::factory()->staff()->create();
        $user->syncModules(['patients']); // No access to financials or aging

        $financialsResponse = $this->actingAs($user)->get(route('financials.index'));
        $financialsResponse->assertStatus(403);

        $agingResponse = $this->actingAs($user)->get(route('aging.index'));
        $agingResponse->assertStatus(403);
    }

    public function test_inactive_user_cannot_access_any_module(): void
    {
        $user = User::factory()->inactive()->create();
        $user->syncModules(['patients']);

        $response = $this->actingAs($user)->get(route('patients.index'));
        $response->assertStatus(403);
    }

    public function test_sidebar_renders_only_accessible_module_links(): void
    {
        $user = User::factory()->staff()->create();
        $user->syncModules(['patients', 'calendar']);

        $response = $this->actingAs($user)->get(route('patients.index'));

        $response->assertStatus(200);
        // Should see granted modules
        $response->assertSee(route('patients.index'));
        $response->assertSee(route('calendar.index'));

        // Should NOT see unauthorized modules in sidebar
        $response->assertDontSee(route('financials.index'));
        $response->assertDontSee(route('aging.index'));
        $response->assertDontSee(route('admin.users.index'));
    }

    public function test_sidebar_renders_admin_user_management_for_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('admin.users.index'));
        $response->assertSee('User & Access', false);
    }
}
