<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ! $user->is_verified && $user->role === 'student') {
            if ($user->status === 'pending') {
                Auth::logout();

                return redirect()->route('login')->with('error', 'Your account is pending verification.');
            }

            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
