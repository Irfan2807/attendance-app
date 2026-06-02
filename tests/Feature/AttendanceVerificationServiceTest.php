<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Services\AttendanceVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_office_ip_matches_exact_ip(): void
    {
        $site = Site::create([
            'name' => 'HQ',
            'ip_address' => '192.168.1.10',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $matched = AttendanceVerificationService::verifyOfficeIp('192.168.1.10');

        $this->assertNotNull($matched);
        $this->assertSame($site->id, $matched->id);
    }

    public function test_verify_office_ip_matches_cidr_range(): void
    {
        $site = Site::create([
            'name' => 'Warehouse',
            'ip_address' => '10.10.10.0/24',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $matched = AttendanceVerificationService::verifyOfficeIp('10.10.10.55');

        $this->assertNotNull($matched);
        $this->assertSame($site->id, $matched->id);
    }

    public function test_verify_office_ip_normalizes_ipv4_mapped_ipv6(): void
    {
        $site = Site::create([
            'name' => 'Branch',
            'ip_address' => '172.16.1.8',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $matched = AttendanceVerificationService::verifyOfficeIp('::ffff:172.16.1.8');

        $this->assertNotNull($matched);
        $this->assertSame($site->id, $matched->id);
    }
}
