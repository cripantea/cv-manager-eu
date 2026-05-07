<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();

        if ($user && $user->is_suspended) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('error', 'Account suspended. Contact your administrator.');
        }

        if (!$user || $user->role !== $role) {
            return redirect('/dashboard')->with('error', 'Unauthorised access.');
        }

        return $next($request);
    }
}
