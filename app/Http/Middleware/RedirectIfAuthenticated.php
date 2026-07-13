<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Jika yang login adalah admin, lempar ke /admin/dashboard
                if ($user->role === 'admin') {
                    return redirect()->route('admin.dashboard');
                }
                // Jika yang login adalah ulp, lempar ke /ulp/dashboard
                if ($user->role === 'ulp') {
                    return redirect()->route('ulp.dashboard');
                }

                return redirect('/');
            }
        }

        return $next($request);
    }
}
