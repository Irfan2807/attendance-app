<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class RoleAccessRoutingTest extends TestCase
{
    use RefreshDatabase;

    private const ROLE_ADMIN = 1;
    private const ROLE_MANAGER = 2;
    private const ROLE_STAFF = 3;
    private const TEST_PASSWORD = 'password';
    private const ADMIN_PHONE = '01111111111';
    private const MANAGER_PHONE = '01111111112';
    private const STAFF_PHONE = '01111111113';

    public function test_panel_access_follows_role_rules(): void
    {
        $admin = User::factory()->create(['role' => self::ROLE_ADMIN]);
        $manager = User::factory()->create(['role' => self::ROLE_MANAGER]);
        $staff = User::factory()->create(['role' => self::ROLE_STAFF]);

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
        $hashedPassword = Hash::make(self::TEST_PASSWORD);
        $admin = User::factory()->create(['role' => self::ROLE_ADMIN, 'phone' => self::ADMIN_PHONE, 'password' => $hashedPassword]);
        $manager = User::factory()->create(['role' => self::ROLE_MANAGER, 'phone' => self::MANAGER_PHONE, 'password' => $hashedPassword]);
        $staff = User::factory()->create(['role' => self::ROLE_STAFF, 'phone' => self::STAFF_PHONE, 'password' => $hashedPassword]);

        $this->loginAndAssertRedirect($admin->phone, '/admin');
        $this->loginAndAssertRedirect($manager->phone, '/staff');
        $this->loginAndAssertRedirect($staff->phone, '/staff');
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

    private function loginAndAssertRedirect(string $phone, string $redirectPath): void
    {
        $this->post(route('login.post'), [
            'login' => $phone,
            'password' => self::TEST_PASSWORD,
        ])->assertRedirect($redirectPath);

        $this->post(route('logout'));
    }
}
