<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Cache static assets for 1 year
        if ($request->path() === 'build' || str_starts_with($request->path(), 'build/')) {
            $response->header('Cache-Control', 'public, max-age=31536000, immutable');
            $response->header('Expires', date('D, d M Y H:i:s T', strtotime('+1 year')));
        }

        // Cache images for 30 days
        if (str_starts_with($request->path(), 'images/')) {
            $response->header('Cache-Control', 'public, max-age=2592000');
            $response->header('Expires', date('D, d M Y H:i:s T', strtotime('+30 days')));
        }

        // Set GZIP compression
        $response->header('Vary', 'Accept-Encoding');

        return $response;
    }
}
