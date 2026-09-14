<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\Role;
use App\Models\LeaveRequest;
use App\Models\PublicHoliday;
use App\Models\User;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicHolidayLeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_holidays_command_fetches_and_persists_holidays(): void
    {
        Http::fake([
            'https://malaysia-holiday.dydxsoft.my/api/v1/holidays*' => Http::response([
                'data' => [
                    [
                        'name' => 'Tahun Baharu',
                        'date' => '2026-01-01',
                        'day_name' => 'Thursday',
                        'state_codes' => ['KUL', 'SGR', 'PNG'],
                        'is_subject_to_change' => false,
                    ],
                    [
                        'name' => 'Hari Kebangsaan',
                        'date' => '2026-08-31',
                        'day_name' => 'Monday',
                        'state_codes' => null,
                        'is_subject_to_change' => false,
                    ],
                ],
            ], 200),
        ]);

        $exitCode = Artisan::call('holidays:sync', ['year' => 2026]);
        $this->assertSame(0, $exitCode);

        $this->assertDatabaseHas('public_holidays', [
            'name' => 'Tahun Baharu',
            'year' => 2026,
            'is_nationwide' => 0,
        ]);

        $this->assertDatabaseHas('public_holidays', [
            'name' => 'Hari Kebangsaan',
            'year' => 2026,
            'is_nationwide' => 1,
        ]);

        $holiday = PublicHoliday::where('name', 'Tahun Baharu')->first();
        $this->assertNotNull($holiday);
        $this->assertEquals('2026-01-01', $holiday->date->toDateString());
        $this->assertEquals(['KUL', 'SGR', 'PNG'], $holiday->state_codes);
    }

    public function test_weekend_detection_for_different_states(): void
    {
        // 2026-02-06 is Friday
        $friday = Carbon::parse('2026-02-06');
        // 2026-02-07 is Saturday
        $saturday = Carbon::parse('2026-02-07');
        // 2026-02-08 is Sunday
        $sunday = Carbon::parse('2026-02-08');

        // Selangor (SGR) -> Saturday and Sunday are weekends
        $this->assertFalse(HolidayService::isWeekend($friday, 'SGR'));
        $this->assertTrue(HolidayService::isWeekend($saturday, 'SGR'));
        $this->assertTrue(HolidayService::isWeekend($sunday, 'SGR'));

        // Kelantan (KTN) -> Friday and Saturday are weekends
        $this->assertTrue(HolidayService::isWeekend($friday, 'KTN'));
        $this->assertTrue(HolidayService::isWeekend($saturday, 'KTN'));
        $this->assertFalse(HolidayService::isWeekend($sunday, 'KTN'));
    }

    public function test_working_days_calculation_excludes_weekends(): void
    {
        // Friday to Monday in Selangor = 4 calendar days (Friday, Saturday, Sunday, Monday)
        // Saturday & Sunday are excluded -> 2 working days
        $start = Carbon::parse('2026-03-13'); // Friday
        $end = Carbon::parse('2026-03-16');   // Monday

        $workingDays = HolidayService::calculateWorkingDays($start, $end, 'SGR');
        $this->assertEquals(2.0, $workingDays);

        $breakdown = HolidayService::getRangeBreakdown($start, $end, 'SGR');
        $this->assertSame(4, $breakdown['total_calendar_days']);
        $this->assertEquals(2.0, $breakdown['working_days']);
        $this->assertSame(2, $breakdown['weekend_days']);
        $this->assertSame(0, $breakdown['public_holiday_days']);
    }

    public function test_working_days_calculation_excludes_public_holidays(): void
    {
        // Create public holiday on Wednesday 2026-04-15
        PublicHoliday::create([
            'name' => 'State Holiday',
            'date' => '2026-04-15',
            'day_name' => 'Wednesday',
            'state_codes' => ['SGR'],
            'is_nationwide' => false,
            'year' => 2026,
        ]);

        // Range: Monday 2026-04-13 to Friday 2026-04-17 (5 calendar days, no weekend in between)
        // Wednesday is public holiday -> 4 working days
        $start = Carbon::parse('2026-04-13');
        $end = Carbon::parse('2026-04-17');

        $workingDays = HolidayService::calculateWorkingDays($start, $end, 'SGR');
        $this->assertEquals(4.0, $workingDays);

        $breakdown = HolidayService::getRangeBreakdown($start, $end, 'SGR');
        $this->assertSame(5, $breakdown['total_calendar_days']);
        $this->assertEquals(4.0, $breakdown['working_days']);
        $this->assertSame(0, $breakdown['weekend_days']);
        $this->assertSame(1, $breakdown['public_holiday_days']);
        $this->assertCount(1, $breakdown['holidays']);
    }

    public function test_public_holiday_model_scopes_and_state_filtering(): void
    {
        $national = PublicHoliday::create([
            'name' => 'Hari Raya Aidilfitri',
            'date' => '2026-03-20',
            'day_name' => 'Friday',
            'is_nationwide' => true,
            'year' => 2026,
        ]);

        $selangorOnly = PublicHoliday::create([
            'name' => 'Hari Keputeraan Sultan Selangor',
            'date' => '2026-12-11',
            'day_name' => 'Friday',
            'state_codes' => ['SGR'],
            'is_nationwide' => false,
            'year' => 2026,
        ]);

        // SGR query should contain both
        $sgrHolidays = PublicHoliday::forState('SGR')->pluck('name')->all();
        $this->assertContains('Hari Raya Aidilfitri', $sgrHolidays);
        $this->assertContains('Hari Keputeraan Sultan Selangor', $sgrHolidays);

        // JHR query should contain national but not Selangor holiday
        $jhrHolidays = PublicHoliday::forState('JHR')->pluck('name')->all();
        $this->assertContains('Hari Raya Aidilfitri', $jhrHolidays);
        $this->assertNotContains('Hari Keputeraan Sultan Selangor', $jhrHolidays);
    }

    public function test_leave_request_smart_working_days_deduction(): void
    {
        $staff = User::factory()->create([
            'role' => Role::Staff,
            'annual_leave_quota' => 14.0,
        ]);

        // Create public holiday on Monday 2026-05-04
        PublicHoliday::create([
            'name' => 'Hari Pekerja Replacement',
            'date' => '2026-05-04',
            'day_name' => 'Monday',
            'is_nationwide' => true,
            'year' => 2026,
        ]);

        // Apply from Friday 2026-05-01 to Tuesday 2026-05-05:
        // 5 calendar days:
        // - Friday 05-01: Labor Day (Public Holiday)
        // - Saturday 05-02: Weekend
        // - Sunday 05-03: Weekend
        // - Monday 05-04: Replacement Holiday (Public Holiday)
        // - Tuesday 05-05: Normal Working Day
        // Legitimate working days deducted should be exactly 1.0 day!
        PublicHoliday::create([
            'name' => 'Hari Pekerja',
            'date' => '2026-05-01',
            'day_name' => 'Friday',
            'is_nationwide' => true,
            'year' => 2026,
        ]);

        $start = '2026-05-01';
        $end = '2026-05-05';

        $workingDays = HolidayService::calculateWorkingDays($start, $end, 'SGR');
        $this->assertEquals(1.0, $workingDays);

        // Create approved leave request with calculated working days
        LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => $start,
            'end_date' => $end,
            'days_count' => $workingDays,
            'status' => LeaveStatus::Approved,
        ]);

        // Assert that remaining leave is 14.0 - 1.0 = 13.0 days (not 14 - 5 = 9 days)
        $this->assertEquals(1.0, $staff->approvedLeaveTaken(LeaveType::AnnualLeave, 2026));
        $this->assertEquals(13.0, $staff->remainingLeave(LeaveType::AnnualLeave, 2026));
    }

    public function test_filament_public_holiday_resource_renders_for_staff_and_admin(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin]);
        $staff = User::factory()->create(['role' => Role::Staff]);

        PublicHoliday::create([
            'name' => 'Hari Malaysia',
            'date' => '2026-09-16',
            'day_name' => 'Wednesday',
            'is_nationwide' => true,
            'year' => 2026,
        ]);

        $adminResponse = $this->actingAs($admin)->get('/admin/public-holidays');
        $adminResponse->assertSuccessful();
        $adminResponse->assertSee('Hari Malaysia');

        $staffResponse = $this->actingAs($staff)->get('/staff/public-holidays');
        $staffResponse->assertSuccessful();
        $staffResponse->assertSee('Hari Malaysia');
    }

    public function test_formatted_state_names_resolves_official_names(): void
    {
        $holiday = PublicHoliday::create([
            'name' => 'Thaipusam',
            'date' => '2026-02-01',
            'day_name' => 'Sunday',
            'state_codes' => ['JHR', 'KUL', 'PNG', 'SGR'],
            'is_nationwide' => false,
            'year' => 2026,
        ]);

        $this->assertSame('Johor, WP Kuala Lumpur, Pulau Pinang, Selangor', $holiday->formattedStateNames());

        $national = PublicHoliday::create([
            'name' => 'Hari Merdeka',
            'date' => '2026-08-31',
            'day_name' => 'Monday',
            'is_nationwide' => true,
            'year' => 2026,
        ]);

        $this->assertSame('All Malaysian states & federal territories', $national->formattedStateNames());
    }
}

