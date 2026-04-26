<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthIntegrationTest extends TestCase
{
    use RefreshDatabase;

    // ----------------------------------------------------------------
    // Login page
    // ----------------------------------------------------------------

    public function test_login_page_is_accessible_to_guests(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_is_redirected_away_from_login_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 3]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/staff');
    }

    public function test_admin_is_redirected_to_admin_panel_from_login_page(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get('/login');

        $response->assertRedirect('/admin');
    }

    // ----------------------------------------------------------------
    // POST /login – successful logins
    // ----------------------------------------------------------------

    public function test_staff_can_login_with_valid_phone_and_password(): void
    {
        /** @var User $staff */
        $staff = User::factory()->create([
            'phone'    => '0123456789',
            'password' => Hash::make('secret'),
            'role'     => 3,
        ]);

        $response = $this->post('/login', [
            'login'    => '0123456789',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/staff');
        $this->assertAuthenticatedAs($staff);
    }

    public function test_manager_is_redirected_to_staff_panel_after_login(): void
    {
        /** @var User $manager */
        $manager = User::factory()->create([
            'phone'    => '0129876543',
            'password' => Hash::make('secret'),
            'role'     => 2,
        ]);

        $response = $this->post('/login', [
            'login'    => '0129876543',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/staff');
        $this->assertAuthenticatedAs($manager);
    }

    public function test_admin_is_redirected_to_admin_panel_after_login(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'phone'    => '0111234567',
            'password' => Hash::make('secret'),
            'role'     => 1,
        ]);

        $response = $this->post('/login', [
            'login'    => '0111234567',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    }

    // ----------------------------------------------------------------
    // POST /login – failed logins
    // ----------------------------------------------------------------

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'phone'    => '0123456789',
            'password' => Hash::make('correct'),
            'role'     => 3,
        ]);

        $response = $this->post('/login', [
            'login'    => '0123456789',
            'password' => 'wrong',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_login_fails_when_user_does_not_exist(): void
    {
        $response = $this->post('/login', [
            'login'    => '0199999999',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_login_fails_with_invalid_phone_format(): void
    {
        $response = $this->post('/login', [
            'login'    => 'not-a-phone',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_login_requires_login_field(): void
    {
        $response = $this->post('/login', [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('login');
    }

    public function test_login_requires_password_field(): void
    {
        $response = $this->post('/login', [
            'login' => '0123456789',
        ]);

        $response->assertSessionHasErrors('password');
    }

    // ----------------------------------------------------------------
    // POST /logout
    // ----------------------------------------------------------------

    public function test_authenticated_user_can_logout(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 3]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
