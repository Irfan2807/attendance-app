<x-filament-widgets::widget>
    <x-filament::section>
        <!-- Top stats row: Today, This Week, This Month -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex items-baseline justify-between">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Today</p>
                </div>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $this->formatMinutes($stats['todayMinutes']) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Completed time today</p>
            </div>
            <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex items-baseline justify-between">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">This Week</p>
                </div>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $this->formatMinutes($stats['weekMinutes']) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Mon to Sun totals</p>
            </div>
            <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex items-baseline justify-between">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">This Month</p>
                </div>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $this->formatMinutes($stats['monthMinutes']) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $stats['completedThisMonth'] }} shifts completed</p>
            </div>
        </div>

        <!-- Shift status -->
        <div class="mt-4 p-3 rounded border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-3">Shift status</p>

            @if ($stats['activeShift'])
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-green-700 dark:text-green-300">Active shift</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-50 mt-1">
                            Started {{ $stats['activeShift']->clock_in_time->format('H:i') }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-200">Working {{ $this->formatMinutes($stats['activeMinutes']) }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-200">{{ $stats['activeShift']->site_name ?? 'Unknown site' }}</p>
                    </div>
                    <div class="text-right text-sm text-green-700 dark:text-green-300">
                        ✓ In progress
                    </div>
                </div>
            @else
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-100">Last shift</p>
                        @if ($stats['lastShift'])
                            <p class="text-sm text-gray-700 dark:text-gray-100">
                                <span class="font-medium">{{ $this->formatTime($stats['lastShift']->clock_in_time) }}</span>
                                <span class="opacity-60">–</span>
                                <span class="font-medium">{{ $this->formatTime($stats['lastShift']->clock_out_time) }}</span>
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-200">Duration {{ $this->formatMinutes($stats['lastShiftMinutes']) }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-200">Status: {{ ucfirst($stats['lastShift']->status ?? 'completed') }}</p>
                        @else
                            <p class="text-sm text-gray-600 dark:text-gray-200">No past shifts recorded</p>
                        @endif
                    </div>
                    @if ($stats['lastShift'])
                        <div class="text-right text-sm text-blue-700 dark:text-blue-300">✓ Completed</div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Approvals and reminders -->
        <div class="mt-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Approvals and reminders</p>
            <div class="grid grid-cols-2 gap-4">
                <div class="p-3 rounded border border-gray-200 dark:border-gray-700">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['pendingCount'] }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Pending approvals</p>
                </div>
                <div class="p-3 rounded border border-gray-200 dark:border-gray-700">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['completedThisMonth'] }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Completed this month</p>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">All clear. Keep clocking in and out on time.</p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
