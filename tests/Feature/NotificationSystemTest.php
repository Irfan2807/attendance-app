<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\Role;
use App\Filament\Staff\Resources\StaffLeaveApprovalResource;
use App\Filament\Staff\Resources\StaffLeaveRequestResource\Pages\CreateStaffLeaveRequest;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AppNotificationService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('staff'));
    }

    public function test_leave_submission_notifies_assigned_manager(): void
    {
        $manager = User::factory()->create([
            'role' => Role::Manager,
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'role' => Role::Staff,
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        Livewire::test(CreateStaffLeaveRequest::class)
            ->fillForm([
                'leave_type' => LeaveType::AnnualLeave->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'days_count' => 2,
                'reason' => 'Family vacation',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Check manager received notification in the notifications table
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $manager->id,
        ]);

        $notification = DB::table('notifications')
            ->where('notifiable_id', $manager->id)
            ->first();

        $data = json_decode($notification->data, true);
        $this->assertEquals('New Leave Application', $data['title']);
        $this->assertStringContainsString($staff->name, $data['body']);
        $this->assertStringContainsString('Annual Leave', $data['body']);
    }

    public function test_leave_submission_by_manager_notifies_director_and_hr(): void
    {
        $director = User::factory()->create([
            'role' => Role::SuperAdmin,
            'is_active' => true,
        ]);

        $hr = User::factory()->create([
            'role' => Role::HR,
            'is_active' => true,
        ]);

        $manager = User::factory()->create([
            'role' => Role::Manager,
            'is_active' => true,
        ]);

        $this->actingAs($manager);

        Livewire::test(CreateStaffLeaveRequest::class)
            ->fillForm([
                'leave_type' => LeaveType::EmergencyLeave->value,
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDay()->toDateString(),
                'days_count' => 1,
                'reason' => 'Family emergency',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Both Director and HR should receive notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $director->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $hr->id,
        ]);
    }

    public function test_leave_approval_notifies_staff_applicant(): void
    {
        $manager = User::factory()->create([
            'role' => Role::Manager,
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'role' => Role::Staff,
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);

        $leave = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::AnnualLeave,
            'status' => LeaveStatus::Pending,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(6),
            'days_count' => 2,
            'reason' => 'Personal matters',
        ]);

        $this->actingAs($manager);

        Livewire::test(StaffLeaveApprovalResource\Pages\ListStaffLeaveApprovals::class)
            ->callTableAction('approve', $leave);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $staff->id,
        ]);

        $notification = DB::table('notifications')
            ->where('notifiable_id', $staff->id)
            ->first();

        $data = json_decode($notification->data, true);
        $this->assertEquals('Leave Request Approved', $data['title']);
        $this->assertStringContainsString($manager->name, $data['body']);
        $this->assertStringContainsString('Annual Leave', $data['body']);
    }

    public function test_leave_rejection_notifies_staff_applicant_with_reason(): void
    {
        $manager = User::factory()->create([
            'role' => Role::Manager,
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'role' => Role::Staff,
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);

        $leave = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::AnnualLeave,
            'status' => LeaveStatus::Pending,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(6),
            'days_count' => 2,
            'reason' => 'Vacation',
        ]);

        $this->actingAs($manager);

        Livewire::test(StaffLeaveApprovalResource\Pages\ListStaffLeaveApprovals::class)
            ->callTableAction('reject', $leave, data: [
                'rejection_reason' => 'Critical project deployment on requested dates',
            ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $staff->id,
        ]);

        $notification = DB::table('notifications')
            ->where('notifiable_id', $staff->id)
            ->first();

        $data = json_decode($notification->data, true);
        $this->assertEquals('Leave Request Rejected', $data['title']);
        $this->assertStringContainsString('Critical project deployment on requested dates', $data['body']);
    }

    public function test_clock_in_pending_approval_notifies_manager(): void
    {
        $manager = User::factory()->create([
            'role' => Role::Manager,
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'role' => Role::Staff,
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);

        $attendance = Attendance::create([
            'user_id' => $staff->id,
            'site_name' => 'Off-site Client Office',
            'latitude' => 3.1390,
            'longitude' => 101.6869,
            'status' => 'pending',
            'clock_in_time' => now(),
            'verification_notes' => 'Awaiting manager approval',
        ]);

        AppNotificationService::notifyClockInPending($attendance);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $manager->id,
        ]);

        $notification = DB::table('notifications')
            ->where('notifiable_id', $manager->id)
            ->first();

        $data = json_decode($notification->data, true);
        $this->assertEquals('Clock-In Awaiting Approval', $data['title']);
        $this->assertStringContainsString($staff->name, $data['body']);
        $this->assertStringContainsString('Off-site Client Office', $data['body']);
    }

    public function test_clock_in_approval_and_rejection_notifies_staff(): void
    {
        $manager = User::factory()->create([
            'role' => Role::Manager,
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'role' => Role::Staff,
            'is_active' => true,
        ]);

        $attendance = Attendance::create([
            'user_id' => $staff->id,
            'site_name' => 'HQ',
            'latitude' => 3.1390,
            'longitude' => 101.6869,
            'status' => 'pending',
            'clock_in_time' => now(),
        ]);

        // 1. Approve notification
        AppNotificationService::notifyClockInApproved($attendance, $manager);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $staff->id,
        ]);

        $approvedNotif = DB::table('notifications')
            ->where('notifiable_id', $staff->id)
            ->first();

        $data = json_decode($approvedNotif->data, true);
        $this->assertEquals('Attendance Approved', $data['title']);
        $this->assertStringContainsString($manager->name, $data['body']);

        // 2. Reject notification
        AppNotificationService::notifyClockInRejected($attendance, $manager, 'Unverified location coordinates');

        $rejectedNotif = DB::table('notifications')
            ->where('notifiable_id', $staff->id)
            ->where('id', '!=', $approvedNotif->id)
            ->first();

        $data = json_decode($rejectedNotif->data, true);
        $this->assertEquals('Attendance Rejected', $data['title']);
        $this->assertStringContainsString('Unverified location coordinates', $data['body']);
    }

    public function test_auto_clock_out_dispatches_notifications_to_user_and_manager(): void
    {
        $manager = User::factory()->create([
            'role' => Role::Manager,
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'role' => Role::Staff,
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);

        // Long running shift 18 hours ago
        $attendance = Attendance::create([
            'user_id' => $staff->id,
            'site_name' => 'HQ',
            'latitude' => 3.1390,
            'longitude' => 101.6869,
            'clock_in_time' => now()->subHours(18),
            'status' => 'approved',
        ]);

        $this->artisan('attendance:auto-clock-out')
            ->assertExitCode(0);

        // Staff received auto clock out alert
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $staff->id,
        ]);

        $staffNotif = DB::table('notifications')
            ->where('notifiable_id', $staff->id)
            ->first();

        $staffData = json_decode($staffNotif->data, true);
        $this->assertEquals('Shift Auto Clocked-Out', $staffData['title']);

        // Manager received infraction alert
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $manager->id,
        ]);

        $managerNotif = DB::table('notifications')
            ->where('notifiable_id', $manager->id)
            ->first();

        $managerData = json_decode($managerNotif->data, true);
        $this->assertEquals('Staff Shift Infraction', $managerData['title']);
        $this->assertStringContainsString($staff->name, $managerData['body']);
    }

    public function test_fleet_compliance_command_dispatches_alerts(): void
    {
        $hr = User::factory()->create([
            'role' => Role::HR,
            'is_active' => true,
        ]);

        // Vehicle with expired road tax and overdue service
        $vehicle = Vehicle::create([
            'name' => 'Toyota Hilux 4x4',
            'numberplate' => 'WXY 9988',
            'current_mileage' => 75000,
            'next_service_mileage' => 70000, // Overdue by 5,000 KM
            'road_tax_expiry' => now()->subDays(3)->toDateString(), // Expired 3 days ago
            'road_tax_amount' => 450.00,
            'is_active' => true,
        ]);

        $this->artisan('fleet:check-alerts')
            ->assertExitCode(0);

        // HR receives alert
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $hr->id,
        ]);

        $notification = DB::table('notifications')
            ->where('notifiable_id', $hr->id)
            ->first();

        $data = json_decode($notification->data, true);
        $this->assertStringContainsString('WXY 9988', $data['title']);
        $this->assertStringContainsString('OVERDUE', $data['body']);
        $this->assertStringContainsString('EXPIRED', $data['body']);
    }

    public function test_filament_panels_have_database_notifications_enabled(): void
    {
        $staffPanel = Filament::getPanel('staff');
        $this->assertTrue($staffPanel->hasDatabaseNotifications());

        $adminPanel = Filament::getPanel('admin');
        $this->assertTrue($adminPanel->hasDatabaseNotifications());
    }
}
