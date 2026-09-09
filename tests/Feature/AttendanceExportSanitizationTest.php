<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceExportSanitizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_export_sanitizes_potential_formula_injection_characters(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin]);
        $suspiciousUser = User::factory()->create([
            'name' => '=cmd|\' /C calc\'!A0',
            'role' => Role::Staff,
        ]);

        Attendance::create([
            'user_id' => $suspiciousUser->id,
            'site_name' => '+Site Malicious',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => now(),
            'clock_out_time' => now()->addHours(8),
        ]);

        $this->actingAs($admin);

        $response = $this->get('/attendance/export');
        $response->assertSuccessful();

        $content = $response->streamedContent();

        // Check that formula triggers have been escaped with a leading single quote
        $this->assertStringContainsString("'=cmd|' /C calc'!A0", $content);
        $this->assertStringContainsString("'+Site Malicious", $content);
    }
}

