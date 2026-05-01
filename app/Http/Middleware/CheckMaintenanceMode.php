<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $maintenanceActive = SystemSetting::where('setting_key', 'maintenance_mode')
            ->value('setting_value') === '1';

        if (! $maintenanceActive) {
            return $next($request);
        }

        // Admin is always allowed through
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        // Allow login routes so non-admins can see the correct message
        // and so admins can log in to turn maintenance off.
        if ($request->is('login') || $request->is('*/login')) {
            return $next($request);
        }

        // Serve the maintenance page for everyone else
        return response()->view('maintenance', [], 503);
    }
}
