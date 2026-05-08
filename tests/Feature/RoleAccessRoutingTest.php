<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RoleAccessRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_access_follows_role_rules(): void
    {
        $admin = User::factory()->make(['role' => 1]);
        $manager = User::factory()->make(['role' => 2]);
        $staff = User::factory()->make(['role' => 3]);

        $adminPanel = $this->panelMock('admin');
        $staffPanel = $this->panelMock('staff');
        $unknownPanel = $this->panelMock('unknown');

        $this->assertTrue($admin->canAccessPanel($adminPanel));
        $this->assertFalse($manager->canAccessPanel($adminPanel));
        $this->assertFalse($staff->canAccessPanel($adminPanel));

        $this->assertFalse($admin->canAccessPanel($staffPanel));
        $this->assertTrue($manager->canAccessPanel($staffPanel));
        $this->assertTrue($staff->canAccessPanel($staffPanel));

        $this->assertFalse($admin->canAccessPanel($unknownPanel));
    }

    public function test_login_redirects_by_role(): void
    {
        $password = 'password'; // UserFactory default password
        $admin = User::factory()->create(['role' => 1, 'phone' => '01111111111']);
        $manager = User::factory()->create(['role' => 2, 'phone' => '01111111112']);
        $staff = User::factory()->create(['role' => 3, 'phone' => '01111111113']);

        $this->post(route('login.post'), [
            'login' => $admin->phone,
            'password' => $password,
        ])->assertRedirect('/admin');

        $this->post(route('logout'));

        $this->post(route('login.post'), [
            'login' => $manager->phone,
            'password' => $password,
        ])->assertRedirect('/staff');

        $this->post(route('logout'));

        $this->post(route('login.post'), [
            'login' => $staff->phone,
            'password' => $password,
        ])->assertRedirect('/staff');
    }

    public function test_guest_is_redirected_to_login_for_export_route(): void
    {
        $this->get(route('attendance.export'))
            ->assertRedirect(route('login'));
    }

    private function panelMock(string $id): Panel
    {
        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('getId')->andReturn($id);

        return $panel;
    }
}
