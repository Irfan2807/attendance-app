<x-filament-widgets::widget>
    <x-filament::section>
        <style>
            .shift-card-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 0.75rem;
                padding-bottom: 0.75rem;
            }
            .shift-card-body {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            @media (min-width: 640px) {
                .shift-card-body {
                    flex-direction: row;
                    align-items: center;
                    justify-content: space-between;
                }
            }
            .shift-btn-clock-in {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                padding: 0.75rem 2rem;
                background-color: #16a34a;
                color: #ffffff;
                font-weight: 700;
                font-size: 0.95rem;
                border-radius: 0.5rem;
                border: 1px solid #15803d;
                cursor: pointer;
                transition: background-color 0.15s ease;
                white-space: nowrap;
            }
            .shift-btn-clock-in:hover {
                background-color: #15803d;
            }
            .shift-btn-clock-out {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                padding: 0.75rem 2rem;
                background-color: #dc2626;
                color: #ffffff;
                font-weight: 700;
                font-size: 0.95rem;
                border-radius: 0.5rem;
                border: 1px solid #b91c1c;
                cursor: pointer;
                transition: background-color 0.15s ease;
                white-space: nowrap;
            }
            .shift-btn-clock-out:hover {
                background-color: #b91c1c;
            }
            .shift-stats-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                text-align: center;
                margin-top: 1.25rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(156, 163, 175, 0.18);
            }
            .shift-stat-divider {
                border-left: 1px solid rgba(156, 163, 175, 0.18);
                border-right: 1px solid rgba(156, 163, 175, 0.18);
            }
        </style>

        {{-- 1. Header: Greeting & Good Standing Badge --}}
        <div class="shift-card-header">
            <div>
                <p style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; font-weight: 600; margin: 0;">
                    {{ now()->translatedFormat('l, j F Y') }}
                </p>
                <h2 style="font-size: 1.4rem; font-weight: 800; color: #ffffff; margin: 2px 0 0; letter-spacing: -0.01em;">
                    Hi, {{ auth()->user()->name }} 👋
                </h2>
            </div>

            <div>
                @if ($isGoodStanding)
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3);">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #22c55e;"></span>
                        Good Standing
                    </span>
                @else
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #f59e0b;"></span>
                        Attendance Notice
                    </span>
                @endif
            </div>
        </div>

        {{-- 2. Middle Row: Shift Info on Left, Action Button on Right --}}
        <div class="shift-card-body"
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
            {{-- Left Side: Status pill & Location --}}
            <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                <div>
                    @if ($isClockedIn && $isPendingApproval)
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);">
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: #f59e0b;"></span>
                            On Duty · Pending Verification ({{ $clockInTime }})
                        </span>
                    @elseif ($isClockedIn)
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3);">
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: #22c55e;"></span>
                            On Duty · Started at {{ $clockInTime }} ({{ $this->formatMinutes($activeShiftMinutes) }})
                        </span>
                    @elseif ($isClockedOut)
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: #3b82f6;"></span>
                            Shift Recorded ({{ $clockInTime }} - {{ $clockOutTime }}) · Pending Approval
                        </span>
                    @elseif ($isCompleted)
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: #3b82f6;"></span>
                            Shift Completed ({{ $clockInTime }} - {{ $clockOutTime }}) · {{ $this->workedHours() }}
                        </span>
                    @else
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; background: rgba(148, 163, 184, 0.12); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25);">
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: #94a3b8;"></span>
                            Ready to Start Shift
                        </span>
                    @endif
                </div>

                <div>
                    @if ($customLocationName)
                        <p style="font-size: 0.8rem; color: #cbd5e1; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <span>📍</span>
                            <span>Assigned Site: <strong style="color: #ffffff;">{{ $customLocationName }}</strong></span>
                        </p>
                    @elseif ($locationError)
                        <p style="font-size: 0.8rem; color: #f59e0b; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <span>⚠️</span>
                            <span>{{ $locationError }}</span>
                        </p>
                    @elseif ($isClockedIn)
                        <p style="font-size: 0.8rem; color: #cbd5e1; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <span>📍</span>
                            <span>Working at: <strong style="color: #ffffff;">{{ $currentShiftLocation ?: 'Headquarters / Registered Site' }}</strong></span>
                        </p>
                    @else
                        <p style="font-size: 0.8rem; color: #94a3b8; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <svg style="width: 14px; height: 14px; opacity: 0.7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Location verified automatically via GPS on tap</span>
                        </p>
                    @endif

                    @if (!$isClockedIn && !$isCompleted && !$isClockedOut)
                        <button
                            wire:click="toggleManualInput"
                            type="button"
                            style="font-size: 0.75rem; color: #34d399; background: none; border: none; padding: 0; margin-top: 4px; cursor: pointer; text-decoration: underline;"
                        >
                            {{ $showManualInput ? '▲ Hide Location Options' : '▼ Working at a client site or off-site?' }}
                        </button>
                    @endif
                </div>
            </div>

            {{-- Right Side: Primary Action Button --}}
            <div style="flex-shrink: 0;">
                @if (!$isClockedIn && !$isCompleted && !$isClockedOut)
                    <button
                        @click="doClockIn()"
                        wire:loading.attr="disabled"
                        type="button"
                        class="shift-btn-clock-in"
                    >
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-show="!locating">Clock In</span>
                        <span x-show="locating" style="display: none;">Detecting GPS...</span>
                    </button>
                @elseif ($isClockedIn)
                    <button
                        wire:click="clockOut"
                        wire:loading.attr="disabled"
                        type="button"
                        class="shift-btn-clock-out"
                    >
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Clock Out</span>
                    </button>
                @elseif ($isCompleted)
                    <button
                        wire:click="clockIn"
                        wire:loading.attr="disabled"
                        type="button"
                        style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 0.6rem 1.2rem; background: rgba(249, 115, 22, 0.15); color: #fb923c; font-size: 0.85rem; font-weight: 600; border-radius: 0.5rem; border: 1px solid rgba(249, 115, 22, 0.35); cursor: pointer;"
                    >
                        <span>⚠️ Clock In Again</span>
                    </button>
                @elseif ($isClockedOut)
                    <div style="padding: 0.5rem 1rem; border-radius: 0.5rem; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); text-align: center;">
                        <p style="font-size: 0.8rem; font-weight: 600; color: #60a5fa; margin: 0;">Shift Pending Review</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Off-Site Drawer if expanded --}}
        @if ($showManualInput && !$isClockedIn && !$isCompleted && !$isClockedOut)
            <div style="margin-top: 1rem; padding: 1rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 0.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
                @if (!empty($availableSites))
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Choose Assigned Work Site:</label>
                        <select 
                            wire:change="selectPredefinedSite($event.target.value)"
                            style="width: 100%; font-size: 0.8rem; border-radius: 0.375rem; border: 1px solid rgba(255, 255, 255, 0.15); background: #1e293b; color: #ffffff; padding: 6px 10px;"
                        >
                            <option value="">-- Select Work Site --</option>
                            @foreach ($availableSites as $site)
                                <option value="{{ $site['id'] }}" @selected($selectedSiteId == $site['id'])>{{ $site['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Or Enter Client / Project Location:</label>
                    <div style="display: flex; gap: 8px;">
                        <input 
                            type="text" 
                            wire:model="customLocationName"
                            placeholder="e.g., Petronas Subang Site"
                            style="flex: 1; font-size: 0.8rem; border-radius: 0.375rem; border: 1px solid rgba(255, 255, 255, 0.15); background: #1e293b; color: #ffffff; padding: 6px 10px;"
                        />
                        <button
                            wire:click="setCustomLocationName"
                            type="button"
                            style="padding: 6px 14px; font-size: 0.75rem; font-weight: 600; background: #16a34a; color: #ffffff; border: none; border-radius: 0.375rem; cursor: pointer;"
                        >
                            Set
                        </button>
                    </div>
                </div>

                <details style="font-size: 0.75rem; color: #94a3b8; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 6px;">
                    <summary style="cursor: pointer;">Advanced: Manual coordinates</summary>
                    <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 6px;">
                        <input type="text" wire:model="manualLatitude" placeholder="Latitude (e.g. 3.069215)" style="font-size: 0.75rem; border-radius: 4px; border: 1px solid rgba(255, 255, 255, 0.15); background: #1e293b; color: #fff; padding: 4px 8px;">
                        <input type="text" wire:model="manualLongitude" placeholder="Longitude (e.g. 101.562021)" style="font-size: 0.75rem; border-radius: 4px; border: 1px solid rgba(255, 255, 255, 0.15); background: #1e293b; color: #fff; padding: 4px 8px;">
                        <button wire:click="useManualCoordinates" type="button" style="padding: 4px 8px; font-size: 0.75rem; font-weight: 600; background: #475569; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Set Coordinates</button>
                    </div>
                </details>
            </div>
        @endif

        {{-- 3. Bottom Row: 3-Column Stats Strip --}}
        <div class="shift-stats-grid">
            <div>
                <span style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Today</span>
                <p style="font-size: 1.35rem; font-weight: 700; color: #ffffff; margin: 2px 0 0;">
                    @if ($isClockedIn)
                        <span style="color: #4ade80;">{{ $this->formatMinutes($todayMinutes + $activeShiftMinutes) }}</span>
                    @else
                        {{ $this->formatMinutes($todayMinutes) }}
                    @endif
                </p>
            </div>
            <div class="shift-stat-divider">
                <span style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">This Week</span>
                <p style="font-size: 1.35rem; font-weight: 700; color: #ffffff; margin: 2px 0 0;">{{ $this->formatMinutes($weekMinutes) }}</p>
            </div>
            <div>
                <span style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">This Month</span>
                <p style="font-size: 1.35rem; font-weight: 700; color: #ffffff; margin: 2px 0 0;">{{ $monthCompletedCount }} <span style="font-size: 0.8rem; font-weight: 400; color: #94a3b8;">shifts</span></p>
            </div>
        </div>
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
