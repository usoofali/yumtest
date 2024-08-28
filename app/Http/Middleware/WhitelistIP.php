<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WhitelistIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $whitelistedIP = '35.242.133.146'; // The IP address to whitelist

        if ($request->ip() !== $whitelistedIP) {
            // Optionally, you can log unauthorized access attempts
            // \Log::warning('Unauthorized access attempt from IP: ' . $request->ip());
            
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
