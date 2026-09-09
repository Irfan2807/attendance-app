<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_login_redirects_to_admin_panel(): void
    {
        $user = User::factory()->create([
            'phone' => '0123456789',
            'password' => bcrypt('password123'),
            'role' => Role::SuperAdmin,
        ]);

        $response = $this->post('/login', [
            'login' => '0123456789',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_staff_login_redirects_to_staff_panel(): void
    {
        $user = User::factory()->create([
            'phone' => '0111111111',
            'password' => bcrypt('password123'),
            'role' => Role::Staff,
        ]);

        $response = $this->post('/login', [
            'login' => '0111111111',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/staff');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_invalid_phone_format(): void
    {
        $response = $this->from('/login')->post('/login', [
            'login' => '12345',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_login_rate_limiting_triggers_after_max_attempts(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', [
                'login' => '0123456789',
                'password' => 'wrongpassword',
            ]);
        }

        // 11th request should be rate-limited (HTTP 429)
        $response = $this->post('/login', [
            'login' => '0123456789',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }
}

