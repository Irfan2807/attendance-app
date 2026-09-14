<?php

namespace App\Services;

use App\Models\PublicHoliday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayService
{
    /**
     * States where the weekend is Friday & Saturday.
     */
    public const FRIDAY_WEEKEND_STATES = ['KTN', 'TRG', 'KDH', 'JHR'];

    /**
     * Official mapping of Malaysian state codes to state names.
     */
    public const STATE_NAMES = [
        'JHR' => 'Johor',
        'KDH' => 'Kedah',
        'KTN' => 'Kelantan',
        'MLK' => 'Melaka',
        'NSN' => 'Negeri Sembilan',
        'PHG' => 'Pahang',
        'PRK' => 'Perak',
        'PLS' => 'Perlis',
        'PNG' => 'Pulau Pinang',
        'SBH' => 'Sabah',
        'SWK' => 'Sarawak',
        'SGR' => 'Selangor',
        'TRG' => 'Terengganu',
        'KUL' => 'WP Kuala Lumpur',
        'LBN' => 'WP Labuan',
        'PJY' => 'WP Putrajaya',
    ];

    /**
     * Get the default Malaysian state from config.
     */
    public static function defaultState(): string
    {
        return config('attendance.holiday_state', 'SGR');
    }

    /**
     * Get weekend days of the week for a given state.
     * 0 = Sunday, 5 = Friday, 6 = Saturday in Carbon dayOfWeek.
     *
     * @return array<int>
     */
    public static function weekendDays(?string $state = null): array
    {
        $code = strtoupper(trim($state ?: self::defaultState()));

        if (in_array($code, self::FRIDAY_WEEKEND_STATES, true)) {
            return [Carbon::FRIDAY, Carbon::SATURDAY];
        }

        return [Carbon::SATURDAY, Carbon::SUNDAY];
    }

    /**
     * Check if a given date is a weekend for the state.
     */
    public static function isWeekend($date, ?string $state = null): bool
    {
        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        return in_array($carbon->dayOfWeek, self::weekendDays($state), true);
    }

    /**
     * Check if a date is a public holiday in the database for the given state.
     */
    public static function isPublicHoliday($date, ?string $state = null): bool
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();
        $code = $state ?: self::defaultState();

        return PublicHoliday::forState($code)
            ->whereDate('date', $dateStr)
            ->exists();
    }

    /**
     * Check if a date is an active working day (neither weekend nor public holiday).
     */
    public static function isWorkingDay($date, ?string $state = null): bool
    {
        return ! self::isWeekend($date, $state) && ! self::isPublicHoliday($date, $state);
    }

    /**
     * Calculate legitimate working days in a date range, excluding weekends and public holidays.
     */
    public static function calculateWorkingDays($startDate, $endDate, ?string $state = null): float
    {
        if (! $startDate || ! $endDate) {
            return 1.0;
        }

        $start = $startDate instanceof Carbon ? $startDate->copy()->startOfDay() : Carbon::parse($startDate)->startOfDay();
        $end = $endDate instanceof Carbon ? $endDate->copy()->startOfDay() : Carbon::parse($endDate)->startOfDay();

        if ($start->gt($end)) {
            return 1.0;
        }

        $workingDays = 0.0;
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $day) {
            if (self::isWorkingDay($day, $state)) {
                $workingDays += 1.0;
            }
        }

        return $workingDays;
    }

    /**
     * Get list of public holidays falling within the date range for user feedback.
     */
    public static function getHolidaysInRange($startDate, $endDate, ?string $state = null): Collection
    {
        if (! $startDate || ! $endDate) {
            return collect();
        }

        $start = $startDate instanceof Carbon ? $startDate->toDateString() : Carbon::parse($startDate)->toDateString();
        $end = $endDate instanceof Carbon ? $endDate->toDateString() : Carbon::parse($endDate)->toDateString();
        $code = $state ?: self::defaultState();

        return PublicHoliday::forState($code)
            ->inDateRange($start, $end)
            ->orderBy('date')
            ->get();
    }

    /**
     * Get detailed breakdown of a date range (working days, weekends, public holidays).
     */
    public static function getRangeBreakdown($startDate, $endDate, ?string $state = null): array
    {
        if (! $startDate || ! $endDate) {
            return [
                'total_calendar_days' => 0,
                'working_days' => 0.0,
                'weekend_days' => 0,
                'public_holiday_days' => 0,
                'holidays' => collect(),
            ];
        }

        $start = $startDate instanceof Carbon ? $startDate->copy()->startOfDay() : Carbon::parse($startDate)->startOfDay();
        $end = $endDate instanceof Carbon ? $endDate->copy()->startOfDay() : Carbon::parse($endDate)->startOfDay();

        if ($start->gt($end)) {
            $end = $start->copy();
        }

        $period = CarbonPeriod::create($start, $end);
        $totalCalendar = 0;
        $workingDays = 0.0;
        $weekendDays = 0;
        $holidayDays = 0;

        foreach ($period as $day) {
            $totalCalendar++;
            $isWk = self::isWeekend($day, $state);
            $isHol = self::isPublicHoliday($day, $state);

            if ($isWk) {
                $weekendDays++;
            } elseif ($isHol) {
                $holidayDays++;
            } else {
                $workingDays += 1.0;
            }
        }

        return [
            'total_calendar_days' => $totalCalendar,
            'working_days' => $workingDays,
            'weekend_days' => $weekendDays,
            'public_holiday_days' => $holidayDays,
            'holidays' => self::getHolidaysInRange($start, $end, $state),
        ];
    }

    /**
     * Sync public holidays from the API for a specific year.
     */
    public static function syncHolidays(int $year, ?string $state = null): int
    {
        $baseUrl = rtrim(config('attendance.holiday_api_url', 'https://malaysia-holiday.dydxsoft.my/api/v1'), '/');
        $url = "{$baseUrl}/holidays?year={$year}";

        if ($state) {
            $url .= "&state=" . urlencode(strtoupper(trim($state)));
        }

        try {
            $response = Http::withoutVerifying()->timeout(15)->get($url);

            if (! $response->successful()) {
                Log::warning("Holiday API returned status {$response->status()} for URL: {$url}");
                return 0;
            }

            $data = $response->json('data');

            if (! is_array($data)) {
                return 0;
            }

            $count = 0;

            foreach ($data as $item) {
                if (empty($item['name']) || empty($item['date'])) {
                    continue;
                }

                $stateCodes = $item['state_codes'] ?? null;
                $isNationwide = empty($stateCodes) || (is_array($stateCodes) && count($stateCodes) >= 16);

                PublicHoliday::updateOrCreate(
                    [
                        'date' => $item['date'],
                        'name' => trim($item['name']),
                    ],
                    [
                        'day_name' => $item['day_name'] ?? Carbon::parse($item['date'])->englishDayOfWeek,
                        'state_codes' => $stateCodes,
                        'is_nationwide' => $isNationwide,
                        'is_subject_to_change' => (bool) ($item['is_subject_to_change'] ?? false),
                        'year' => $year,
                    ]
                );

                $count++;
            }

            Log::info("Successfully synced {$count} public holidays for year {$year}");

            return $count;
        } catch (\Throwable $e) {
            Log::error("Failed to sync public holidays from API: {$e->getMessage()}");
            return 0;
        }
    }
}
