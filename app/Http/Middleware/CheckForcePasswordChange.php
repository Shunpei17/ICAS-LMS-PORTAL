<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckForcePasswordChange
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->registration_source === 'csv_import' && auth()->user()->force_password_reset) {
            $user = auth()->user();
            
            // Define allowed routes based on role
            $allowedRoutes = ['logout'];
            
            if ($user->role === 'admin') {
                $allowedRoutes[] = 'admin.settings';
                $allowedRoutes[] = 'admin.settings.password.update';
                $targetRoute = 'admin.settings';
            } elseif ($user->role === 'faculty') {
                $allowedRoutes[] = 'faculty.settings';
                $allowedRoutes[] = 'faculty.settings.password';
                $targetRoute = 'faculty.settings';
            } else {
                // Students
                $allowedRoutes[] = 'student.settings';
                $allowedRoutes[] = 'password.change'; // Fallback
                $allowedRoutes[] = 'password.update'; // Fallback
                $targetRoute = 'student.settings';
            }

            if ($request->routeIs($allowedRoutes)) {
                return $next($request);
            }

            return redirect()->route($targetRoute)
                ->with('status', 'For your security, please change your password before proceeding.');
        }

        return $next($request);
    }
}
