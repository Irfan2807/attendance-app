<x-filament-widgets::widget>
    <x-filament::section>

        <!-- Status -->
        <div class="mb-6 p-4 rounded-lg border-l-4
            @if ($isClockedIn && !$isPendingApproval)
                bg-green-50 dark:bg-green-900/20 border-green-500 dark:border-green-600
            @elseif ($isClockedIn && $isPendingApproval)
                bg-orange-50 dark:bg-orange-900/20 border-orange-500 dark:border-orange-600
            @elseif ($isClockedOut)
                bg-orange-50 dark:bg-orange-900/20 border-orange-500 dark:border-orange-600
            @elseif ($isCompleted)
                bg-blue-50 dark:bg-blue-900/20 border-blue-500 dark:border-blue-600
            @else
                bg-red-50 dark:bg-red-900/20 border-red-500 dark:border-red-600
            @endif
        ">
            @if ($isClockedIn && $isPendingApproval)
                <p class="text-sm font-semibold text-orange-800 dark:text-orange-300 mb-1">⏳ Clocked In - Pending Approval</p>
                <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $clockInTime }}</p>
                <p class="text-xs text-orange-700 dark:text-orange-400 mt-2">Your clock-in is awaiting manager verification, but you can clock out normally</p>
            @elseif ($isClockedIn)
                <p class="text-sm font-semibold text-green-800 dark:text-green-300 mb-1">✓ Currently Clocked In</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $clockInTime }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">Working since this time</p>
            @elseif ($isClockedOut)
                <p class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">✓ Shift Completed</p>
                <div class="space-y-1">
                    <p class="text-sm">
                        <span class="text-gray-700 dark:text-gray-300">Clock In:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $clockInTime }}</span>
                    </p>
                    <p class="text-sm">
                        <span class="text-gray-700 dark:text-gray-300">Clock Out:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $clockOutTime }}</span>
                    </p>
                </div>
                <p class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-3">{{ $this->workedHours() }} worked</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-3">Pending manager approval</p>
            @elseif ($isCompleted)
                <p class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">✓ Shift Completed</p>
                <div class="space-y-1">
                    <p class="text-sm">
                        <span class="text-gray-700 dark:text-gray-300">Clock In:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $clockInTime }}</span>
                    </p>
                    <p class="text-sm">
                        <span class="text-gray-700 dark:text-gray-300">Clock Out:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $clockOutTime }}</span>
                    </p>
                </div>
                <p class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-3">{{ $this->workedHours() }} worked</p>
            @else
                <p class="text-sm font-semibold text-red-800 dark:text-red-300">✗ Not Clocked In</p>
                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">Click the button below to start your shift</p>
            @endif
        </div>

        <!-- Time Cards -->
        <div class="grid grid-cols-2 gap-3 mb-6" wire:key="time-cards">
            <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Clock In</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $clockInTime ?? '—' }}</p>
            </div>
            <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Clock Out</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $clockOutTime ?? '—' }}</p>
            </div>
        </div>

        <!-- Location Status -->
        <div class="mb-4 p-3 rounded-lg border
            @if ($locationError)
                bg-red-50 dark:bg-red-900/20 border-red-500 dark:border-red-600 text-red-900 dark:text-red-300
            @elseif ($latitude && $longitude)
                bg-green-50 dark:bg-green-900/20 border-green-500 dark:border-green-600 text-green-900 dark:text-green-300
            @else
                bg-orange-50 dark:bg-orange-900/20 border-orange-500 dark:border-orange-600 text-orange-900 dark:text-orange-300
            @endif
        ">
            @if ($locationError)
                <p class="text-sm font-semibold">✗ Location Error</p>
                <p class="text-xs mt-1">{{ $locationError }}</p>
            @elseif ($latitude && $longitude)
                <p class="text-sm font-semibold">✓ Location Captured</p>
                <p class="text-xs mt-1">Lat: {{ $latitude }}, Lon: {{ $longitude }}</p>
                <p class="text-xs mt-1 opacity-70">Your IP: {{ $clientIp }}</p>
            @else
                <p class="text-sm font-semibold">📍 Location Not Yet Captured</p>
                <p class="text-xs mt-1">Click "Get Location" or enter manually</p>
                <p class="text-xs mt-1 opacity-70">Your IP: {{ $clientIp }}</p>
            @endif
        </div>

        <!-- Manual Coordinate Input (for testing when browser location is wrong) -->
        @if ($showManualInput && !$isClockedIn)
            <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <p class="text-sm font-semibold text-yellow-900 dark:text-yellow-300 mb-2">🧪 Manual Coordinates (Testing)</p>
                <div class="space-y-2">
                    <input 
                        type="text" 
                        wire:model="manualLatitude"
                        placeholder="Latitude (e.g., 3.069215)"
                        class="w-full px-3 py-2 text-sm border border-yellow-300 rounded"
                    />
                    <input 
                        type="text" 
                        wire:model="manualLongitude"
                        placeholder="Longitude (e.g., 101.562021)"
                        class="w-full px-3 py-2 text-sm border border-yellow-300 rounded"
                    />
                    <button
                        wire:click="useManualCoordinates"
                        type="button"
                        class="w-full px-3 py-2 text-sm font-semibold bg-yellow-500 hover:bg-yellow-600 text-white rounded"
                    >
                        ✓ Use These Coordinates
                    </button>
                </div>
            </div>
        @endif

        <!-- Action Button -->
        <div class="mb-4 space-y-2" wire:key="action-button" x-data="{ 
            getLocation() {
                console.log('🔵 Get Location button clicked!');
                console.log('Protocol:', window.location.protocol);
                console.log('Hostname:', window.location.hostname);
                
                const isSecure = window.location.protocol === 'https:' || ['localhost', '127.0.0.1'].includes(window.location.hostname);
                console.log('Is secure context:', isSecure);

                if (!isSecure) {
                    console.warn('⚠️ Not a secure context');
                    $wire.call('setLocationError', 'Geolocation requires HTTPS or localhost.');
                    return;
                }

                if (!navigator.geolocation) {
                    console.error('❌ Geolocation not supported');
                    $wire.call('setLocationError', 'Geolocation is not supported by your browser.');
                    return;
                }

                console.log('📍 Requesting geolocation...');
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude.toString();
                        const lon = position.coords.longitude.toString();
                        console.log('✅ Location captured:', lat, lon);
                        console.log('Accuracy:', position.coords.accuracy, 'meters');
                        $wire.call('setLocation', lat, lon);
                    },
                    (error) => {
                        console.error('❌ Geolocation error:', error.code, error.message);
                        let errorMsg = 'Unable to get location';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMsg = 'Location permission denied. Please allow location access in browser settings.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMsg = 'Location information is unavailable.';
                                break;
                            case error.TIMEOUT:
                                errorMsg = 'Location request timed out. Please try again.';
                                break;
                        }
                        console.error('Error message:', errorMsg);
                        $wire.call('setLocationError', errorMsg);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            }
        }">
            @if (!$isClockedIn && !$isCompleted && !$isClockedOut)
                <button
                    @click="getLocation()"
                    type="button"
                    style="background-color: #3b82f6; border: 2px solid #1e40af; color: #ffffff; cursor: pointer;"
                    onmouseover="this.style.backgroundColor='#2563eb'"
                    onmouseout="this.style.backgroundColor='#3b82f6'"
                    class="w-full px-4 py-2 text-sm font-semibold rounded-lg transition duration-200 inline-flex items-center justify-center gap-2"
                >
                    <span>📍 Get Location</span>
                </button>
                <button
                    wire:click="toggleManualInput"
                    type="button"
                    style="background-color: #f59e0b; border: 2px solid #d97706; color: #ffffff; cursor: pointer;"
                    onmouseover="this.style.backgroundColor='#d97706'"
                    onmouseout="this.style.backgroundColor='#f59e0b'"
                    class="w-full px-4 py-2 text-sm font-semibold rounded-lg transition duration-200 inline-flex items-center justify-center gap-2"
                >
                    <span>{{ $showManualInput ? '✗ Cancel Manual Input' : '✏️ Enter Coordinates Manually' }}</span>
                </button>
                <button
                    wire:click="clockIn"
                    wire:loading.attr="disabled"
                    type="button"
                    style="background-color: #22c55e; border: 2px solid #15803d; color: #ffffff; cursor: pointer;"
                    onmouseover="this.style.backgroundColor='#16a34a'"
                    onmouseout="this.style.backgroundColor='#22c55e'"
                    class="w-full px-4 py-3 font-semibold rounded-lg transition duration-200 inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove>🕐 Clock In</span>
                    <span wire:loading>⏳ Processing...</span>
                </button>
            @elseif ($isClockedIn && $isPendingApproval)
                <div class="p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded text-center mb-3">
                    <p class="text-xs text-orange-700 dark:text-orange-400">⏳ Clock-in pending manager approval. You can still clock out.</p>
                </div>
                <button
                    wire:click="clockOut"
                    wire:loading.attr="disabled"
                    type="button"
                    style="background-color: #ef4444; border: 2px solid #b91c1c; color: #ffffff; cursor: pointer;"
                    onmouseover="this.style.backgroundColor='#dc2626'"
                    onmouseout="this.style.backgroundColor='#ef4444'"
                    class="w-full px-4 py-3 font-semibold rounded-lg transition duration-200 inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove>🕐 Clock Out</span>
                    <span wire:loading>⏳ Processing...</span>
                </button>
            @elseif ($isClockedIn)
                <button
                    wire:click="clockOut"
                    wire:loading.attr="disabled"
                    type="button"
                    style="background-color: #ef4444; border: 2px solid #b91c1c; color: #ffffff; cursor: pointer;"
                    onmouseover="this.style.backgroundColor='#dc2626'"
                    onmouseout="this.style.backgroundColor='#ef4444'"
                    class="w-full px-4 py-3 font-semibold rounded-lg transition duration-200 inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove>🕐 Clock Out</span>
                    <span wire:loading>⏳ Processing...</span>
                </button>
            @elseif ($isClockedOut)
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded text-center">
                    <p class="text-sm font-semibold text-blue-900 dark:text-blue-300">✓ Shift Complete - Pending Approval</p>
                    <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">Your shift has been saved. Manager will review shortly.</p>
                </div>
            @elseif ($isCompleted)
                <div class="space-y-3">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded text-center">
                        <p class="text-sm font-semibold text-blue-900 dark:text-blue-300">Your shift is complete for today</p>
                        <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">✓ See you tomorrow!</p>
                    </div>
                    <button
                        wire:click="clockIn"
                        wire:loading.attr="disabled"
                        type="button"
                        style="background-color: #f97316; border: 2px solid #9a3412; color: #ffffff; cursor: pointer;"
                        onmouseover="this.style.backgroundColor='#ea580c'"
                        onmouseout="this.style.backgroundColor='#f97316'"
                        class="w-full px-4 py-2 text-sm font-semibold rounded-lg transition duration-200 inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove>⚠️ Clock In Again (Requires Approval)</span>
                        <span wire:loading>⏳ Processing...</span>
                    </button>
                </div>
            @endif
        </div>

        <!-- Info Message -->
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded">
            <p class="text-sm text-blue-900 dark:text-blue-300">
                <strong>💡 Reminder:</strong> Always clock out before leaving. Required for accurate payroll.
            </p>
        </div>
    </x-filament::section>

    <script>
        // Auto-refresh widget at midnight (00:00)
        function scheduleMiddightRefresh() {
            const now = new Date();
            const tomorrow = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0);
            const msUntilMidnight = tomorrow - now;

            setTimeout(() => {
                // Refresh the Livewire component
                const component = Livewire.find('{{ $this->getId() }}');
                if (component) {
                    component.call('$refresh');
                }
                // Schedule next refresh
                scheduleMiddightRefresh();
            }, msUntilMidnight);
        }

        // Start the midnight refresh scheduler
        document.addEventListener('DOMContentLoaded', () => {
            scheduleMiddightRefresh();
        });
    </script>
</x-filament-widgets::widget>


