<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Hero Split Layout: Left Info, Right Action --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            {{-- Left Column: Greeting, Shift Status & Location --}}
            <div class="space-y-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ now()->translatedFormat('l, j F Y') }}
                    </p>
                    <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight mt-0.5">
                        Hi, {{ auth()->user()->name }} 👋
                    </h2>
                </div>

                {{-- Status Pill --}}
                <div class="flex items-center gap-3">
                    @if ($isClockedIn && $isPendingApproval)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            On Duty · Pending Manager Verification ({{ $clockInTime }})
                        </span>
                    @elseif ($isClockedIn)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700/60">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            On Duty · Started at {{ $clockInTime }} ({{ $this->formatMinutes($activeShiftMinutes) }})
                        </span>
                    @elseif ($isClockedOut)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-300 dark:border-blue-700/60">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Shift Recorded ({{ $clockInTime }} - {{ $clockOutTime }}) · Pending Approval
                        </span>
                    @elseif ($isCompleted)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-300 dark:border-blue-700/60">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Shift Completed ({{ $clockInTime }} - {{ $clockOutTime }}) · {{ $this->workedHours() }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-300 dark:border-gray-700">
                            <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                            Ready to Start Shift
                        </span>
                    @endif
                </div>

                {{-- Location Subtitle --}}
                <div>
                    @if ($customLocationName)
                        <p class="text-xs text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                            <span class="text-blue-500">📍</span>
                            <span>Assigned Site: <strong class="font-semibold text-gray-900 dark:text-white">{{ $customLocationName }}</strong></span>
                        </p>
                    @elseif ($locationError)
                        <p class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                            <span>⚠️</span>
                            <span>{{ $locationError }}</span>
                        </p>
                    @elseif ($isClockedIn)
                        <p class="text-xs text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                            <span class="text-emerald-500">📍</span>
                            <span>Working at: <strong class="font-semibold text-gray-900 dark:text-white">{{ $currentShiftLocation ?: 'Headquarters / Registered Site' }}</strong></span>
                        </p>
                    @else
                        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Location verified automatically via GPS on tap</span>
                        </p>
                    @endif

                    {{-- Off-site collapsible toggle --}}
                    @if (!$isClockedIn && !$isCompleted && !$isClockedOut)
                        <div class="mt-1">
                            <button
                                wire:click="toggleManualInput"
                                type="button"
                                class="text-xs font-medium text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300 underline cursor-pointer"
                            >
                                {{ $showManualInput ? '▲ Hide Location Options' : '▼ Working at a client site or off-site?' }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column: Action Button & Standing Badge --}}
            <div class="flex flex-col items-center sm:items-end justify-center gap-2.5"
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
                @if (!$isClockedIn && !$isCompleted && !$isClockedOut)
                    <button
                        @click="doClockIn()"
                        wire:loading.attr="disabled"
                        type="button"
                        style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-width: 220px; padding: 14px 28px; background: linear-gradient(135deg, #22c55e, #16a34a); color: #ffffff; font-size: 1.1rem; font-weight: 700; border-radius: 12px; border: 1px solid #15803d; box-shadow: 0 8px 18px -4px rgba(34, 197, 94, 0.45); cursor: pointer; transition: all 0.2s ease;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 22px -4px rgba(34, 197, 94, 0.6)';"
                        onmouseout="this.style.transform='none'; this.style.boxShadow='0 8px 18px -4px rgba(34, 197, 94, 0.45)';"
                    >
                        <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-show="!locating">Clock In</span>
                        <span x-show="locating" style="display: none;">Detecting GPS...</span>
                    </button>
                @elseif ($isClockedIn)
                    <button
                        wire:click="clockOut"
                        wire:loading.attr="disabled"
                        type="button"
                        style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-width: 220px; padding: 14px 28px; background: linear-gradient(135deg, #ef4444, #dc2626); color: #ffffff; font-size: 1.1rem; font-weight: 700; border-radius: 12px; border: 1px solid #b91c1c; box-shadow: 0 8px 18px -4px rgba(239, 68, 68, 0.45); cursor: pointer; transition: all 0.2s ease;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 22px -4px rgba(239, 68, 68, 0.6)';"
                        onmouseout="this.style.transform='none'; this.style.boxShadow='0 8px 18px -4px rgba(239, 68, 68, 0.45)';"
                    >
                        <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Clock Out</span>
                    </button>
                @elseif ($isCompleted)
                    <button
                        wire:click="clockIn"
                        wire:loading.attr="disabled"
                        type="button"
                        style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-width: 220px; padding: 12px 24px; background: rgba(249, 115, 22, 0.15); color: #f97316; font-size: 0.9rem; font-weight: 600; border-radius: 10px; border: 1px solid rgba(249, 115, 22, 0.35); cursor: pointer; transition: all 0.2s ease;"
                    >
                        <span>⚠️ Clock In Again</span>
                    </button>
                @elseif ($isClockedOut)
                    <div style="padding: 10px 18px; border-radius: 10px; background: rgba(59, 130, 246, 0.12); border: 1px solid rgba(59, 130, 246, 0.25); text-align: center;">
                        <p style="font-size: 0.82rem; font-weight: 600; color: #60a5fa; margin: 0;">Shift Pending Review</p>
                    </div>
                @endif

                {{-- Disciplinary Standing Badge --}}
                <div>
                    @if ($isGoodStanding)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Good Standing
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Attendance Notice
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Off-Site Drawer if expanded --}}
        @if ($showManualInput && !$isClockedIn && !$isCompleted && !$isClockedOut)
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/70 rounded-xl space-y-3">
                @if (!empty($availableSites))
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Choose Assigned Work Site:</label>
                        <select 
                            wire:change="selectPredefinedSite($event.target.value)"
                            class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-xs"
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
                            class="flex-1 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-xs"
                        />
                        <button
                            wire:click="setCustomLocationName"
                            type="button"
                            class="px-3.5 py-1.5 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-xs cursor-pointer"
                        >
                            Set
                        </button>
                    </div>
                </div>

                <details class="text-[11px] text-gray-500 dark:text-gray-400 pt-1 border-t border-gray-200 dark:border-gray-700">
                    <summary class="cursor-pointer hover:underline">Advanced: Manual coordinates</summary>
                    <div class="space-y-1.5 mt-2">
                        <input type="text" wire:model="manualLatitude" placeholder="Latitude (e.g. 3.069215)" class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        <input type="text" wire:model="manualLongitude" placeholder="Longitude (e.g. 101.562021)" class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        <button wire:click="useManualCoordinates" type="button" class="w-full py-1 text-xs font-semibold bg-gray-600 text-white rounded hover:bg-gray-700 cursor-pointer">Set Coordinates</button>
                    </div>
                </details>
            </div>
        @endif

        {{-- Footer Metrics Strip: 3 horizontal stats --}}
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); text-align: center; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid rgba(156, 163, 175, 0.18);">
            <div>
                <p style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; font-weight: 600; margin: 0;">Today</p>
                <p style="font-size: 1.35rem; font-weight: 800; margin: 4px 0 0;" class="text-gray-900 dark:text-white">
                    @if ($isClockedIn)
                        <span style="color: #22c55e;">{{ $this->formatMinutes($todayMinutes + $activeShiftMinutes) }}</span>
                    @else
                        {{ $this->formatMinutes($todayMinutes) }}
                    @endif
                </p>
            </div>
            <div style="border-left: 1px solid rgba(156, 163, 175, 0.18); border-right: 1px solid rgba(156, 163, 175, 0.18);">
                <p style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; font-weight: 600; margin: 0;">This Week</p>
                <p style="font-size: 1.35rem; font-weight: 800; margin: 4px 0 0;" class="text-gray-900 dark:text-white">{{ $this->formatMinutes($weekMinutes) }}</p>
            </div>
            <div>
                <p style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; font-weight: 600; margin: 0;">This Month</p>
                <p style="font-size: 1.35rem; font-weight: 800; margin: 4px 0 0;" class="text-gray-900 dark:text-white">{{ $monthCompletedCount }} <span style="font-size: 0.8rem; font-weight: 400; color: #9ca3af;">shifts</span></p>
            </div>
        </div>

        {{-- Subtle Footer Reminder --}}
        <p style="text-align: center; font-size: 0.7rem; color: #9ca3af; margin: 12px 0 0;">
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
