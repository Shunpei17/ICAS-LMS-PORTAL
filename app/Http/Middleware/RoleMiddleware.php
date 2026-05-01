<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Check if user is logged in
        if (! Auth::check()) {
            return redirect('/login');
        }

        // 2. Check if the user's role matches the required role
        if (Auth::user()->role !== $role) {
            // If they are a student trying to reach /admin, send them to their own dashboard
            return redirect(Auth::user()->role.'/dashboard')
                ->with('error', 'You do not have permission to access that page.');
        }

        return $next($request);
    }
}
