<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeenAt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only update for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            
            // Update last_seen_at timestamp
            // Using DB query to avoid triggering model events and observers
            DB::table('users')
                ->where('id', $user->id)
                ->update(['last_seen_at' => now()]);
        }

        return $next($request);
    }
}
