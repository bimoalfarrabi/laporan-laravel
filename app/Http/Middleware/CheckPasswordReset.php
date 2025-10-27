<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordReset
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user() && Auth::user()->must_reset_password) {
            // Izinkan akses ke route perubahan password, baik GET maupun POST
            // Periksa nama route yang sedang diakses
            $routeName = $request->route() ? $request->route()->getName() : null;

            if ($routeName === 'password.force-change' || $routeName === 'password.update-forced') {
                return $next($request);
            }

            // Jika tidak, arahkan ke halaman perubahan password
            return redirect()->route('password.force-change');
        }

        return $next($request);
    }
}
