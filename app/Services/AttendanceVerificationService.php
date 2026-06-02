<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Http\Request;

class AttendanceVerificationService
{
    private const BYTE_MASK = 0xFF;

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

        $normalizedClientIp = self::normalizeIp($clientIp);

        if (! $normalizedClientIp) {
            return null;
        }

        // Fast path: exact match first.
        $exactMatch = Site::where('ip_address', $normalizedClientIp)
            ->where('is_active', true)
            ->first();

        if ($exactMatch) {
            return $exactMatch;
        }

        // Fallback: support CIDR ranges and minor input formatting issues.
        $matchedSite = null;

        Site::where('is_active', true)
            ->whereNotNull('ip_address')
            ->orderBy('id')
            ->chunkById(200, function ($sites) use ($normalizedClientIp, &$matchedSite) {
                foreach ($sites as $site) {
                    if (self::ipMatchesRule($normalizedClientIp, $site->ip_address)) {
                        $matchedSite = $site;

                        return false;
                    }
                }
            });

        return $matchedSite;
    }

    private static function normalizeIp(?string $ip): ?string
    {
        if (! $ip) {
            return null;
        }

        $ip = trim($ip);

        // Normalize IPv4-mapped IPv6 (::ffff:192.168.1.10) into plain IPv4.
        if (str_starts_with(strtolower($ip), '::ffff:')) {
            $mappedIpv4 = substr($ip, 7);
            if (filter_var($mappedIpv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return $mappedIpv4;
            }
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }

    private static function ipMatchesRule(string $clientIp, ?string $rule): bool
    {
        if (! $rule) {
            return false;
        }

        $rule = trim($rule);
        if ($rule === '') {
            return false;
        }

        // Exact IP match
        $ruleIp = self::normalizeIp($rule);
        if ($ruleIp && $ruleIp === $clientIp) {
            return true;
        }

        // CIDR range match (e.g. 192.168.1.0/24)
        if (! str_contains($rule, '/')) {
            return false;
        }

        [$network, $prefix] = explode('/', $rule, 2);
        $network = self::normalizeIp($network);
        $prefix = trim($prefix);

        if (! $network || ! is_numeric($prefix)) {
            return false;
        }

        $prefix = (int) $prefix;
        $clientBinary = inet_pton($clientIp);
        $networkBinary = inet_pton($network);

        if ($clientBinary === false || $networkBinary === false || strlen($clientBinary) !== strlen($networkBinary)) {
            return false;
        }

        $maxPrefix = strlen($networkBinary) * 8;
        if ($prefix < 0 || $prefix > $maxPrefix) {
            return false;
        }

        $fullBytes = intdiv($prefix, 8);
        $remainingBits = $prefix % 8;

        if (strncmp($clientBinary, $networkBinary, $fullBytes) !== 0) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (self::BYTE_MASK << (8 - $remainingBits)) & self::BYTE_MASK;

        return ((ord($clientBinary[$fullBytes]) & $mask) === (ord($networkBinary[$fullBytes]) & $mask));
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
