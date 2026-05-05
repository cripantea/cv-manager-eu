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

            return redirect('/login')->with('error', 'Account sospeso. Contatta l\'amministratore.');
        }

        if (!$user || $user->role !== $role) {
            return redirect('/dashboard')->with('error', 'Accesso non autorizzato.');
        }

        return $next($request);
    }
}
