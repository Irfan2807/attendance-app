<x-filament-widgets::widget>
    <x-filament::section class="overflow-hidden">

        {{-- 1. Header Bar: Friendly Greeting & Standing Pill --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-5 border-b border-gray-100 dark:border-gray-800">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                    {{ now()->translatedFormat('l, j F Y') }}
                </p>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-0.5">
                    Hi, {{ auth()->user()->name }} 👋
                </h2>
            </div>

            <div class="flex items-center gap-2">
                @if ($isGoodStanding)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Good Standing
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800/80 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Attendance Notice
                    </span>
                @endif
            </div>
        </div>

        {{-- 2. Hero Center: Clean Primary Shift Action & Clear Status --}}
        <div class="py-6 px-4 my-4 bg-gray-50/70 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800 text-center"
             x-data="{ 
                locating: false,
                doClockIn() {
                    if ($wire.latitude || $wire.isManualLocation) {
                        $wire.call('clockIn');
                        return;
                    }

                    const isSecure = window.location.protocol === 'https:' || ['localhost', '127.0.0.1'].includes(window.location.hostname);
                    if (!navigator.geolocation || !isSecure) {
                        $wire.call('clockIn');
                        return;
                    }

                    this.locating = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.locating = false;
                            $wire.call('setLocation', pos.coords.latitude.toString(), pos.coords.longitude.toString());
                            $wire.call('clockIn');
                        },
                        (err) => {
                            this.locating = false;
                            let errorMsg = err.code === err.PERMISSION_DENIED
                                ? 'Location permission denied. Verification by network.'
                                : 'Unable to capture GPS. Verification by network.';
                            $wire.call('setLocationError', errorMsg);
                            $wire.call('clockIn');
                        },
                        { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
                    );
                }
             }"
        >
            {{-- Shift Status Pill --}}
            <div class="mb-4">
                @if ($isClockedIn && $isPendingApproval)
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-800">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        On Duty · Pending Manager Verification ({{ $clockInTime }})
                    </span>
                @elseif ($isClockedIn)
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        On Duty · Started at {{ $clockInTime }}
                    </span>
                @elseif ($isClockedOut)
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-800">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        Shift Recorded ({{ $clockInTime }} - {{ $clockOutTime }}) · Pending Approval
                    </span>
                @elseif ($isCompleted)
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-800">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        Shift Completed ({{ $clockInTime }} - {{ $clockOutTime }}) · {{ $this->workedHours() }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700/70 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                        Ready to Start Shift
                    </span>
                @endif
            </div>

            {{-- Primary Punch Buttons --}}
            <div class="max-w-md mx-auto">
                @if (!$isClockedIn && !$isCompleted && !$isClockedOut)
                    <button
                        @click="doClockIn()"
                        wire:loading.attr="disabled"
                        type="button"
                        class="w-full py-4 px-6 text-lg font-bold text-white rounded-xl shadow-md transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        <span x-show="!locating" wire:loading.remove class="flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Clock In
                        </span>
                        <span x-show="locating" style="display: none;" class="flex items-center gap-2">
                            <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Detecting Location...
                        </span>
                        <span wire:loading class="flex items-center gap-2">
                            <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Starting Shift...
                        </span>
                    </button>
                @elseif ($isClockedIn)
                    <button
                        wire:click="clockOut"
                        wire:loading.attr="disabled"
                        type="button"
                        class="w-full py-4 px-6 text-lg font-bold text-white rounded-xl shadow-md transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2.5 bg-rose-600 hover:bg-rose-500 active:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        <span wire:loading.remove class="flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Clock Out
                        </span>
                        <span wire:loading class="flex items-center gap-2">
                            <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Ending Shift...
                        </span>
                    </button>
                @elseif ($isCompleted)
                    <div class="space-y-3">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-xl">
                            <p class="text-sm font-semibold text-blue-900 dark:text-blue-300">Shift Completed for Today</p>
                            <p class="text-xs text-blue-700 dark:text-blue-400 mt-0.5">✓ Great job! See you tomorrow.</p>
                        </div>
                        <button
                            wire:click="clockIn"
                            wire:loading.attr="disabled"
                            type="button"
                            class="w-full py-2.5 px-4 text-xs font-semibold text-orange-700 dark:text-orange-300 bg-orange-50 dark:bg-orange-950/40 hover:bg-orange-100 dark:hover:bg-orange-900/40 border border-orange-200 dark:border-orange-800 rounded-lg transition cursor-pointer"
                        >
                            <span wire:loading.remove>⚠️ Clock In Again (Requires Approval)</span>
                            <span wire:loading>Processing...</span>
                        </button>
                    </div>
                @elseif ($isClockedOut)
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-xl">
                        <p class="text-sm font-semibold text-blue-900 dark:text-blue-300">Shift Recorded · Pending Manager Review</p>
                        <p class="text-xs text-blue-700 dark:text-blue-400 mt-0.5">Your shift has been recorded and will be verified shortly.</p>
                    </div>
                @endif
            </div>

            {{-- Calm Location Status Indicator --}}
            <div class="mt-3">
                @if ($customLocationName)
                    <p class="text-xs text-gray-600 dark:text-gray-300 flex items-center justify-center gap-1.5">
                        <span class="text-blue-500">📍</span>
                        <span>Assigned Work Site: <strong class="font-semibold text-gray-900 dark:text-white">{{ $customLocationName }}</strong></span>
                    </p>
                @elseif ($locationError)
                    <p class="text-xs text-amber-600 dark:text-amber-400 flex items-center justify-center gap-1.5">
                        <span>⚠️</span>
                        <span>{{ $locationError }}</span>
                    </p>
                @elseif ($isClockedIn)
                    <p class="text-xs text-gray-600 dark:text-gray-300 flex items-center justify-center gap-1.5">
                        <span class="text-emerald-500">📍</span>
                        <span>Working at: <strong class="font-semibold text-gray-900 dark:text-white">{{ $currentShiftLocation ?: 'Office / Registered Site' }}</strong></span>
                    </p>
                @elseif ($latitude && $longitude)
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 flex items-center justify-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>GPS Captured · Ready to clock in</span>
                    </p>
                @else
                    <p class="text-xs text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Location automatically verified upon tap</span>
                    </p>
                @endif
            </div>

            {{-- Collapsible Off-Site / Client Project Selector (Only shown before clock in) --}}
            @if (!$isClockedIn && !$isCompleted && !$isClockedOut)
                <div class="mt-2 pt-1">
                    <button
                        wire:click="toggleManualInput"
                        type="button"
                        class="text-xs font-medium text-gray-600 hover:text-emerald-600 dark:text-gray-400 dark:hover:text-emerald-400 transition cursor-pointer"
                    >
                        {{ $showManualInput ? '▲ Hide Location Options' : '▼ Working at a client site or off-site?' }}
                    </button>

                    @if ($showManualInput)
                        <div class="mt-3 max-w-md mx-auto p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl space-y-3 text-left shadow-sm">
                            @if (!empty($availableSites))
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Choose Assigned Work Site:</label>
                                    <select 
                                        wire:change="selectPredefinedSite($event.target.value)"
                                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    >
                                        <option value="">-- Select Work Site --</option>
                                        @foreach ($availableSites as $site)
                                            <option value="{{ $site['id'] }}" @selected($selectedSiteId == $site['id'])>{{ $site['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Or Enter Client / Project Location:</label>
                                <div class="flex gap-2">
                                    <input 
                                        type="text" 
                                        wire:model="customLocationName"
                                        placeholder="e.g., Petronas Subang Site"
                                        class="flex-1 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    />
                                    <button
                                        wire:click="setCustomLocationName"
                                        type="button"
                                        class="px-3.5 py-1.5 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition cursor-pointer"
                                    >
                                        Set
                                    </button>
                                </div>
                            </div>

                            <details class="text-[11px] text-gray-600 dark:text-gray-400 pt-1 border-t border-gray-100 dark:border-gray-700">
                                <summary class="cursor-pointer hover:underline">Advanced: Manual coordinates</summary>
                                <div class="space-y-1.5 mt-2">
                                    <input type="text" wire:model="manualLatitude" placeholder="Latitude (e.g. 3.069215)" class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    <input type="text" wire:model="manualLongitude" placeholder="Longitude (e.g. 101.562021)" class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    <button wire:click="useManualCoordinates" type="button" class="w-full py-1 text-xs font-semibold bg-gray-600 text-white rounded hover:bg-gray-700 cursor-pointer">Set Coordinates</button>
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- 3. Footer Metrics Strip: 3-column quiet stats --}}
        <div class="pt-4 border-t border-gray-100 dark:border-gray-800 grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-800 text-center">
            <div>
                <p class="text-[11px] uppercase tracking-wider font-semibold text-gray-600 dark:text-gray-400">Today</p>
                <p class="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                    @if ($isClockedIn)
                        <span class="text-emerald-600 dark:text-emerald-400">{{ $this->formatMinutes($todayMinutes + $activeShiftMinutes) }}</span>
                    @else
                        {{ $this->formatMinutes($todayMinutes) }}
                    @endif
                </p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wider font-semibold text-gray-600 dark:text-gray-400">This Week</p>
                <p class="text-base font-bold text-gray-900 dark:text-white mt-0.5">{{ $this->formatMinutes($weekMinutes) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wider font-semibold text-gray-600 dark:text-gray-400">This Month</p>
                <p class="text-base font-bold text-gray-900 dark:text-white mt-0.5">{{ $monthCompletedCount }} <span class="text-xs font-normal text-gray-600 dark:text-gray-400">shifts</span></p>
            </div>
        </div>

        {{-- 4. Discreet Reminder Notice --}}
        <p class="text-center text-[11px] text-gray-600 dark:text-gray-400 mt-4">
            💡 Reminder: Always clock out when leaving to keep your attendance verified.
        </p>
    </x-filament::section>

    <script>
        function scheduleMidnightRefresh() {
            const now = new Date();
            const tomorrow = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0);
            const msUntilMidnight = tomorrow - now;

            setTimeout(() => {
                const component = Livewire.find('{{ $this->getId() }}');
                if (component) {
                    component.call('$refresh');
                }
                scheduleMidnightRefresh();
            }, msUntilMidnight);
        }

        document.addEventListener('DOMContentLoaded', () => {
            scheduleMidnightRefresh();
        });
    </script>
</x-filament-widgets::widget>
