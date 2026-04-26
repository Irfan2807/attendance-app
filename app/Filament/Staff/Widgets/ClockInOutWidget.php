<?php

namespace App\Filament\Staff\Widgets;

use App\Models\Attendance;
use App\Services\AttendanceWindowService;
use App\Services\AttendanceVerificationService;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Attributes\Lazy;

#[Lazy]
class ClockInOutWidget extends Widget
{
    protected static string $view = 'filament.staff.widgets.clock-in-out-widget';

    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = [
        'default' => 1,
        'lg' => 1,
    ];

    public bool $isLoading = false;
    public ?string $latitude = null;
    public ?string $longitude = null;
    public bool $isClockedIn = false;
    public bool $isClockedOut = false;
    public bool $isCompleted = false;
    public bool $isPendingApproval = false;
    public ?string $clockInTime = null;
    public ?string $clockOutTime = null;
    public ?string $locationError = null;
    public bool $showManualInput = false;
    public ?string $manualLatitude = null;
    public ?string $manualLongitude = null;
    public ?string $clientIp = null;

    public function mount(): void
    {
        $this->loadAttendanceState();
        $this->clientIp = request()->ip();
    }

    public function hydrate(): void
    {
        // Reload state whenever the component is rehydrated
        $this->loadAttendanceState();
        if (!$this->clientIp) {
            $this->clientIp = request()->ip();
        }
    }

    #[On('refresh-widget')]
    public function refresh(): void
    {
        // Clear cache when refreshing to get fresh data
        $this->clearWidgetCaches();
        $this->loadAttendanceState();
    }

    private function clearWidgetCaches(): void
    {
        $userId = Auth::id();

        Cache::forget('attendance_state_' . $userId);
        Cache::forget('clock_in_details_stats_' . $userId);
        Cache::forget('staff_stats_' . $userId);
    }

    public function loadAttendanceState(): void
    {
        // Cache attendance state for 2 minutes to reduce database queries
        $cacheKey = 'attendance_state_' . Auth::user()->id;
        
        $attendanceState = Cache::remember($cacheKey, 120, function () {
            $userId = Auth::user()->id;
            $now = now();

            // Check if there is an active shift first.
            $activeShift = Attendance::where('user_id', $userId)
                ->whereNull('clock_out_time')
                ->orderBy('clock_in_time', 'desc')
                ->first();

            // Auto-close stale shifts that exceeded max configured shift hours.
            if ($activeShift && AttendanceWindowService::isStaleShift($activeShift->clock_in_time, $now)) {
                $autoClockOut = $activeShift->clock_in_time->copy()->addHours(AttendanceWindowService::maxShiftHours());
                $notesPrefix = $activeShift->verification_notes ? $activeShift->verification_notes . ' | ' : '';

                $activeShift->update([
                    'clock_out_time' => $autoClockOut,
                    'status' => 'temporary',
                    'verification_notes' => $notesPrefix . 'Auto-closed stale shift after max shift duration',
                ]);

                $activeShift = null;
            }

            if ($activeShift) {
                return $activeShift;
            }

            $todayStart = $now->copy()->startOfDay();
            $todayEnd = $now->copy()->endOfDay();

            // No active shift: consider today's latest attendance.
            return Attendance::where('user_id', $userId)
                ->whereBetween('clock_in_time', [$todayStart, $todayEnd])
                ->orderBy('clock_in_time', 'desc')
                ->first();
        });

        // Reset all states first
        $this->isClockedIn = false;
        $this->isClockedOut = false;
        $this->isCompleted = false;
        $this->isPendingApproval = false;
        $this->clockInTime = null;
        $this->clockOutTime = null;

        // No record found - ready to clock in
        if (!$attendanceState) {
            return;
        }

        // Set times
        $this->clockInTime = $attendanceState->clock_in_time->format('H:i');
        $this->clockOutTime = $attendanceState->clock_out_time ? $attendanceState->clock_out_time->format('H:i') : null;

        // Has NOT clocked out yet - currently working
        if (!$attendanceState->clock_out_time) {
            $this->isClockedIn = true;
            $this->isPendingApproval = ($attendanceState->status === 'pending');
            return;
        }

        // Has clocked out - check if completed or pending
        if (in_array($attendanceState->status, ['approved', 'completed'])) {
            $this->isCompleted = true;
        } else {
            $this->isClockedOut = true;
        }
    }

    public function setLocation($latitude, $longitude): void
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->locationError = null;
    }

    public function useManualCoordinates(): void
    {
        if ($this->manualLatitude && $this->manualLongitude) {
            $this->latitude = $this->manualLatitude;
            $this->longitude = $this->manualLongitude;
            $this->locationError = null;
            $this->showManualInput = false;
            
            $this->dispatch('notify', 
                title: '✓ Manual Coordinates Set', 
                message: "Lat: {$this->latitude}, Lon: {$this->longitude}",
                status: 'success'
            );
        }
    }

    public function toggleManualInput(): void
    {
        $this->showManualInput = !$this->showManualInput;
    }

    public function requestLocation(): void
    {
        // This triggers the JS to request location
        $this->dispatch('request-geolocation');
    }

    public function setLocationError($message): void
    {
        $this->locationError = $message;
    }

    public function clockIn(): void
    {
        // Reload state first to ensure we have latest data
        $this->loadAttendanceState();

        $user = Auth::user();

        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        // Check if there's already a completed shift today
        $completedShift = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in_time', [$todayStart, $todayEnd])
            ->whereNotNull('clock_out_time')
            ->first();

        // Check if already clocked in but not out (across days)
        $activeShift = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out_time')
            ->orderBy('clock_in_time', 'desc')
            ->first();

        if ($activeShift) {
            $this->dispatch('notify', 
                title: 'Already Clocked In', 
                message: 'You are already clocked in. Please clock out first.',
                status: 'warning'
            );
            return;
        }

        $this->isLoading = true;

        try {
            // Get client IP
            $clientIp = AttendanceVerificationService::getClientIp();
            
            // Step 1: Check IP Address
            $ipVerified = AttendanceVerificationService::verifyOfficeIp($clientIp);
            
            // Step 2: Check Location (if IP not verified)
            $locationVerified = null;
            if (!$ipVerified && ($this->latitude && $this->longitude)) {
                $locationVerified = AttendanceVerificationService::verifyOfficeLocation(
                    $this->latitude,
                    $this->longitude
                );
            }

            // Determine verification status
            $status = 'pending'; // Default: requires approval
            $verificationNotes = [];

            // If this is an additional shift (already completed one today), always require approval
            if ($completedShift) {
                $status = 'pending';
                $verificationNotes[] = "Additional shift - Requires manager approval";
                $verificationNotes[] = "Previous shift: {$completedShift->clock_in_time->format('H:i')} - {$completedShift->clock_out_time->format('H:i')}";
            } elseif ($ipVerified) {
                $status = 'approved';
                $verificationNotes[] = "IP Verified: {$clientIp}";
            } elseif ($locationVerified) {
                $status = 'approved';
                $verificationNotes[] = "Location Verified: {$locationVerified->name}";
            } else {
                // Step 3: Check Group Verification (5+ staff within 50m in last 2 hours)
                $groupVerified = false;
                if ($this->latitude && $this->longitude) {
                    $groupVerified = AttendanceVerificationService::verifyGroupClockIn(
                        $this->latitude,
                        $this->longitude,
                        50, // 50 meter radius
                        5,  // minimum 5 staff
                        2   // within last 2 hours
                    );
                }

                if ($groupVerified) {
                    $status = 'approved';
                    $verificationNotes[] = "Group Verified: 5+ staff nearby";
                    $verificationNotes[] = "Location: {$this->latitude}, {$this->longitude}";
                } else {
                    $verificationNotes[] = "IP: {$clientIp}";
                    if ($this->latitude && $this->longitude) {
                        $verificationNotes[] = "Location: {$this->latitude}, {$this->longitude}";
                    }
                    $verificationNotes[] = "Awaiting manager approval";
                }
            }

            // Create attendance record
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'site_name' => $ipVerified?->name ?? $locationVerified?->name ?? 'Unknown Location',
                'latitude' => $this->latitude ? (float)$this->latitude : 0,
                'longitude' => $this->longitude ? (float)$this->longitude : 0,
                'status' => $status,
                'clock_in_time' => now(),
                'verification_notes' => implode(' | ', $verificationNotes),
            ]);

            // Update component state to reflect database changes
            $this->isClockedIn = true;
            $this->isClockedOut = false;
            $this->isCompleted = false;
            $this->isPendingApproval = ($status === 'pending');
            $this->clockInTime = $attendance->clock_in_time->format('H:i');
            $this->clockOutTime = null;
            $this->clearWidgetCaches();

            // Send notification based on status
            if ($status === 'approved') {
                $this->dispatch('notify', 
                    title: '✓ Clocked In Successfully', 
                    message: 'Time: ' . now()->format('H:i'),
                    status: 'success'
                );
            } else {
                $notificationMessage = $completedShift 
                    ? 'Additional shift requires manager approval'
                    : 'Your clock-in requires manager verification';
                    
                $this->dispatch('notify', 
                    title: '⏳ Pending Manager Approval', 
                    message: $notificationMessage,
                    status: 'warning'
                );
            }

        } catch (\Exception $e) {
            $this->dispatch('notify', 
                title: '✗ Error', 
                message: 'Failed to clock in: ' . $e->getMessage(),
                status: 'error'
            );
        } finally {
            $this->isLoading = false;
        }
    }

    public function clockOut(): void
    {
        // Double check if not clocked in
        if (!$this->isClockedIn) {
            $this->dispatch('notify', 
                title: 'Not Clocked In', 
                message: 'Please clock in first.',
                status: 'warning'
            );
            return;
        }

        $this->isLoading = true;

        try {
            $user = Auth::user();

            // Find today's attendance that hasn't been clocked out
            $todayAttendance = Attendance::where('user_id', $user->id)
                ->whereNull('clock_out_time')
                ->orderBy('clock_in_time', 'desc')
                ->first();

            if (!$todayAttendance) {
                $this->dispatch('notify', 
                    title: 'Not Clocked In', 
                    message: 'Please clock in first.',
                    status: 'warning'
                );
                $this->isLoading = false;
                return;
            }

            // Update with clock out time
            // Always mark as 'temporary' when clocking out - manager approval happens later
            $clockOutTime = now();
            $todayAttendance->update([
                'clock_out_time' => $clockOutTime,
                'status' => 'temporary',
            ]);
            $this->clearWidgetCaches();

            // Update component state to reflect database changes
            $this->isClockedIn = false;
            $this->isClockedOut = false;
            $this->isCompleted = true;
            $this->clockOutTime = $clockOutTime->format('H:i');
            
            $this->dispatch('notify', 
                title: '✓ Clocked Out Successfully', 
                message: 'Time: ' . $this->clockOutTime,
                status: 'success'
            );

        } catch (\Exception $e) {
            $this->dispatch('notify', 
                title: '✗ Error', 
                message: 'Failed to clock out: ' . $e->getMessage(),
                status: 'error'
            );
        } finally {
            $this->isLoading = false;
        }
    }

    public function workedHours()
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $attendance = Attendance::where('user_id', Auth::user()->id)
            ->whereBetween('clock_in_time', [$todayStart, $todayEnd])
            ->orderBy('clock_in_time', 'desc')
            ->first();
        
        if (!$attendance || !$attendance->clock_out_time) {
            return '0m';
        }

        $hours = $attendance->clock_in_time->diffInHours($attendance->clock_out_time);
        $minutes = $attendance->clock_in_time->diffInMinutes($attendance->clock_out_time) % 60;

        if ($hours < 1) {
            return "{$minutes}m";
        }

        return "{$hours}h {$minutes}m";
    }
}
