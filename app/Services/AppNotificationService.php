<?php

namespace App\Services;

use App\Enums\LeaveType;
use App\Enums\Role;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class AppNotificationService
{
    /**
     * Notify supervisors/HR when an employee submits a new leave application.
     */
    public static function notifyLeaveSubmitted(LeaveRequest $leave): void
    {
        $applicant = $leave->user;
        if (! $applicant) {
            return;
        }

        $recipients = collect();

        // If the applicant is a manager, notify Directors and HR Executives
        if ($applicant->isManager()) {
            $recipients = User::query()
                ->where('is_active', true)
                ->whereIn('role', [Role::SuperAdmin, Role::HR])
                ->where('id', '!=', $applicant->id)
                ->get();
        } elseif ($applicant->manager && $applicant->manager->is_active) {
            // Field staff with an assigned manager
            $recipients = collect([$applicant->manager]);
        } else {
            // Unassigned staff: notify all active Managers, HR, and Directors
            $recipients = User::query()
                ->where('is_active', true)
                ->whereIn('role', [Role::Manager, Role::HR, Role::SuperAdmin])
                ->where('id', '!=', $applicant->id)
                ->get();
        }

        if ($recipients->isEmpty()) {
            return;
        }

        $leaveTypeName = $leave->leave_type instanceof LeaveType
            ? $leave->leave_type->label()
            : (LeaveType::tryFrom((string) $leave->leave_type)?->label() ?? 'Leave');

        $dateRange = $leave->start_date && $leave->end_date
            ? "{$leave->start_date->format('d M')} - {$leave->end_date->format('d M')}"
            : 'dates specified';

        $days = (float) $leave->days_count;

        $notification = Notification::make()
            ->title('New Leave Application')
            ->body("{$applicant->name} applied for {$leaveTypeName} ({$days} day(s), {$dateRange}).")
            ->icon('heroicon-o-calendar')
            ->iconColor('warning')
            ->actions([
                Action::make('review')
                    ->label('Review')
                    ->url('/staff/leave-approvals')
                    ->button()
                    ->markAsRead(),
            ]);

        $notification->sendToDatabase($recipients);
    }

    /**
     * Notify the applicant that their leave request has been approved.
     */
    public static function notifyLeaveApproved(LeaveRequest $leave, ?User $approver = null): void
    {
        $applicant = $leave->user;
        if (! $applicant) {
            return;
        }

        $approverName = $approver?->name ?? 'Management';
        $leaveTypeName = $leave->leave_type instanceof LeaveType
            ? $leave->leave_type->label()
            : (LeaveType::tryFrom((string) $leave->leave_type)?->label() ?? 'Leave');

        $days = (float) $leave->days_count;

        $notification = Notification::make()
            ->title('Leave Request Approved')
            ->body("Your {$leaveTypeName} ({$days} day(s)) has been approved by {$approverName}.")
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->actions([
                Action::make('view')
                    ->label('View My Leaves')
                    ->url('/staff/my-leaves')
                    ->button()
                    ->markAsRead(),
            ]);

        $notification->sendToDatabase($applicant);
    }

    /**
     * Notify the applicant that their leave request has been rejected.
     */
    public static function notifyLeaveRejected(LeaveRequest $leave, ?User $approver = null, ?string $reason = null): void
    {
        $applicant = $leave->user;
        if (! $applicant) {
            return;
        }

        $approverName = $approver?->name ?? 'Management';
        $leaveTypeName = $leave->leave_type instanceof LeaveType
            ? $leave->leave_type->label()
            : (LeaveType::tryFrom((string) $leave->leave_type)?->label() ?? 'Leave');

        $reasonText = $reason ?: ($leave->rejection_reason ?? 'Not specified');

        $notification = Notification::make()
            ->title('Leave Request Rejected')
            ->body("Your {$leaveTypeName} was rejected by {$approverName}. Reason: {$reasonText}")
            ->icon('heroicon-o-x-circle')
            ->iconColor('danger')
            ->actions([
                Action::make('view')
                    ->label('View My Leaves')
                    ->url('/staff/my-leaves')
                    ->button()
                    ->markAsRead(),
            ]);

        $notification->sendToDatabase($applicant);
    }

    /**
     * Notify supervisors when a clock-in requires verification (off-site, manual override, etc.).
     */
    public static function notifyClockInPending(Attendance $attendance): void
    {
        $staff = $attendance->user;
        if (! $staff) {
            return;
        }

        $recipients = collect();

        if ($staff->manager && $staff->manager->is_active) {
            $recipients = collect([$staff->manager]);
        } else {
            $recipients = User::query()
                ->where('is_active', true)
                ->whereIn('role', [Role::Manager, Role::HR, Role::SuperAdmin])
                ->where('id', '!=', $staff->id)
                ->get();
        }

        if ($recipients->isEmpty()) {
            return;
        }

        $location = $attendance->site_name ?: 'Off-site / Manual Location';

        $notification = Notification::make()
            ->title('Clock-In Awaiting Approval')
            ->body("{$staff->name} clocked in at {$location} requiring verification.")
            ->icon('heroicon-o-clock')
            ->iconColor('warning')
            ->actions([
                Action::make('review')
                    ->label('Review')
                    ->url('/staff/clock-in-approvals')
                    ->button()
                    ->markAsRead(),
            ]);

        $notification->sendToDatabase($recipients);
    }

    /**
     * Notify staff member that their clock-in has been approved.
     */
    public static function notifyClockInApproved(Attendance $attendance, ?User $approver = null): void
    {
        $staff = $attendance->user;
        if (! $staff) {
            return;
        }

        $approverName = $approver?->name ?? 'Management';
        $shiftDate = $attendance->clock_in_time ? $attendance->clock_in_time->format('d M Y') : 'your shift';

        $notification = Notification::make()
            ->title('Attendance Approved')
            ->body("Your attendance record for {$shiftDate} was approved by {$approverName}.")
            ->icon('heroicon-o-check-circle')
            ->iconColor('success');

        $notification->sendToDatabase($staff);
    }

    /**
     * Notify staff member that their clock-in has been rejected.
     */
    public static function notifyClockInRejected(Attendance $attendance, ?User $approver = null, ?string $reason = null): void
    {
        $staff = $attendance->user;
        if (! $staff) {
            return;
        }

        $approverName = $approver?->name ?? 'Management';
        $shiftDate = $attendance->clock_in_time ? $attendance->clock_in_time->format('d M Y') : 'your shift';
        $notes = $reason ?: ($attendance->approval_notes ?? '');

        $body = "Your attendance record for {$shiftDate} was rejected by {$approverName}.";
        if ($notes !== '') {
            $body .= " Reason: {$notes}";
        }

        $notification = Notification::make()
            ->title('Attendance Rejected')
            ->body($body)
            ->icon('heroicon-o-x-circle')
            ->iconColor('danger');

        $notification->sendToDatabase($staff);
    }

    /**
     * Notify staff and supervisor when a shift is auto-closed due to exceeding max hours.
     */
    public static function notifyAutoClockOut(Attendance $attendance, int $hours): void
    {
        $staff = $attendance->user;
        if (! $staff) {
            return;
        }

        $shiftDate = $attendance->clock_in_time ? $attendance->clock_in_time->format('d M h:i A') : 'your shift';

        // 1. Alert staff member
        $staffNotification = Notification::make()
            ->title('Shift Auto Clocked-Out')
            ->body("Your shift starting at {$shiftDate} was automatically closed after {$hours} hours due to incomplete clock-out.")
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('danger');

        $staffNotification->sendToDatabase($staff);

        // 2. Alert supervisor if assigned
        if ($staff->manager && $staff->manager->is_active) {
            $managerNotification = Notification::make()
                ->title('Staff Shift Infraction')
                ->body("{$staff->name} failed to clock out (shift auto-closed after {$hours} hours).")
                ->icon('heroicon-o-exclamation-triangle')
                ->iconColor('warning')
                ->actions([
                    Action::make('review')
                        ->label('Review')
                        ->url('/staff/clock-in-approvals')
                        ->button()
                        ->markAsRead(),
                ]);

            $managerNotification->sendToDatabase($staff->manager);
        }
    }

    /**
     * Notify HR Executives and Admins about a fleet vehicle compliance alert.
     */
    public static function notifyFleetComplianceAlert(Vehicle $vehicle, array $alerts): void
    {
        $recipients = User::query()
            ->where('is_active', true)
            ->whereIn('role', [Role::Manager, Role::HR, Role::SuperAdmin])
            ->get();

        if ($recipients->isEmpty() || empty($alerts)) {
            return;
        }

        $alertsText = implode('; ', $alerts);

        $notification = Notification::make()
            ->title("Fleet Compliance: {$vehicle->numberplate}")
            ->body("Vehicle {$vehicle->name} ({$vehicle->numberplate}): {$alertsText}.")
            ->icon('heroicon-o-truck')
            ->iconColor('danger')
            ->actions([
                Action::make('view_fleet')
                    ->label('View Fleet')
                    ->url('/staff/vehicles')
                    ->button()
                    ->markAsRead(),
            ]);

        $notification->sendToDatabase($recipients);
    }
}
