<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Http\Request;

class AttendanceVerificationService
{
    /**
     * Get client IP address from request.
     * Uses Laravel's built-in ip() method which respects configured trusted proxies,
     * preventing IP spoofing via client-controlled headers like X-Forwarded-For.
     */
    public static function getClientIp(Request $request = null): ?string
    {
        return ($request ?? request())->ip();
    }

    /**
     * Check if IP address matches any office location
     */
    public static function verifyOfficeIp($clientIp): ?Site
    {
        if (!$clientIp) {
            return null;
        }

        // Find site matching the client IP
        return Site::where('ip_address', $clientIp)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Check if coordinates are within any office location radius
     * Uses Haversine formula for distance calculation
     */
    public static function verifyOfficeLocation($latitude, $longitude, $radiusMeters = null): ?Site
    {
        if (!$latitude || !$longitude) {
            return null;
        }

        $sites = Site::where('is_active', true)->get();

        foreach ($sites as $site) {
            $distance = self::haversineDistance(
                $latitude,
                $longitude,
                $site->latitude,
                $site->longitude
            );

            $allowedRadius = $radiusMeters ?? $site->radius_meters ?? 100;

            if ($distance <= $allowedRadius) {
                return $site;
            }
        }

        return null;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in meters
     */
    private static function haversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * asin(sqrt($a));

        return $earthRadius * $c; // distance in meters
    }

    /**
     * Check if 5+ staff have clocked in within 50m radius in the last 2 hours
     * Group verification: if multiple staff are at same location, likely legitimate
     */
    public static function verifyGroupClockIn($latitude, $longitude, $radiusMeters = 50, $minStaff = 5, $timeWindowHours = 2): bool
    {
        if (!$latitude || !$longitude) {
            return false;
        }

        $recentAttendances = \App\Models\Attendance::where('clock_in_time', '>=', now()->subHours($timeWindowHours))
            ->where('status', 'approved')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $nearbyCount = 0;

        foreach ($recentAttendances as $attendance) {
            $distance = self::haversineDistance(
                $latitude,
                $longitude,
                $attendance->latitude,
                $attendance->longitude
            );

            if ($distance <= $radiusMeters) {
                $nearbyCount++;
            }

            // Early exit if threshold met
            if ($nearbyCount >= $minStaff) {
                return true;
            }
        }

        return false;
    }
}
