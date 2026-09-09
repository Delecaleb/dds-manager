<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_user_management_index(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Admin & Access Privilege Management', false);
    }

    public function test_non_super_admin_cannot_view_user_management_index(): void
    {
        $staffUser = User::factory()->staff()->create();

        $response = $this->actingAs($staffUser)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_render_create_user_screen(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertSee('Add New User');
    }

    public function test_super_admin_can_create_new_user_with_assigned_modules(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Dr. Jane Smith',
            'email' => 'dr.jane@dds-manager.local',
            'password' => 'SecurePass123!',
            'role' => 'provider',
            'is_active' => '1',
            'modules' => ['patients', 'provider-portal', 'kpis'],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Dr. Jane Smith',
            'email' => 'dr.jane@dds-manager.local',
            'role' => 'provider',
            'is_active' => true,
        ]);

        $newUser = User::where('email', 'dr.jane@dds-manager.local')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->hasModuleAccess('patients'));
        $this->assertTrue($newUser->hasModuleAccess('provider-portal'));
        $this->assertTrue($newUser->hasModuleAccess('kpis'));
        $this->assertFalse($newUser->hasModuleAccess('financials'));
    }

    public function test_non_super_admin_cannot_create_user(): void
    {
        $staffUser = User::factory()->staff()->create();

        $response = $this->actingAs($staffUser)->post(route('admin.users.store'), [
            'name' => 'Hacker User',
            'email' => 'hacker@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'hacker@example.com']);
    }

    public function test_super_admin_can_edit_user_and_update_modules(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $staffUser = User::factory()->staff()->create();
        $staffUser->syncModules(['calendar']);

        $response = $this->actingAs($superAdmin)->put(route('admin.users.update', $staffUser), [
            'name' => 'Updated Staff Name',
            'email' => 'updated.staff@dds-manager.local',
            'role' => 'manager',
            'is_active' => '1',
            'modules' => ['calendar', 'front-office', 'operations'],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $staffUser->refresh();
        $this->assertSame('Updated Staff Name', $staffUser->name);
        $this->assertSame('updated.staff@dds-manager.local', $staffUser->email);
        $this->assertSame('manager', $staffUser->role);
        $this->assertTrue($staffUser->hasModuleAccess('front-office'));
        $this->assertTrue($staffUser->hasModuleAccess('operations'));
    }

    public function test_super_admin_cannot_demote_own_account(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->put(route('admin.users.update', $superAdmin), [
            'name' => $superAdmin->name,
            'email' => $superAdmin->email,
            'role' => 'staff',
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors('role');
        $superAdmin->refresh();
        $this->assertSame('super_admin', $superAdmin->role);
    }

    public function test_super_admin_can_toggle_user_active_status(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $targetUser = User::factory()->staff()->create(['is_active' => true]);

        $response = $this->actingAs($superAdmin)->patch(route('admin.users.toggle-status', $targetUser));

        $response->assertRedirect();
        $targetUser->refresh();
        $this->assertFalse($targetUser->is_active);

        // Toggle back to active
        $this->actingAs($superAdmin)->patch(route('admin.users.toggle-status', $targetUser));
        $targetUser->refresh();
        $this->assertTrue($targetUser->is_active);
    }

    public function test_super_admin_cannot_deactivate_own_account(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['is_active' => true]);

        $response = $this->actingAs($superAdmin)->patch(route('admin.users.toggle-status', $superAdmin));

        $superAdmin->refresh();
        $this->assertTrue($superAdmin->is_active);
        $response->assertSessionHas('error');
    }

    public function test_super_admin_can_delete_user(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $targetUser = User::factory()->staff()->create();

        $response = $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $targetUser));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_super_admin_cannot_delete_own_account(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $superAdmin));

        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
        $response->assertSessionHas('error');
    }
}
