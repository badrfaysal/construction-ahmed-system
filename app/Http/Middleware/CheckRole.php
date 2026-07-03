<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Checks that the logged-in user has the required role.
// Usage in routes: ->middleware('role:admin')
class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // If user doesn't have the required role, redirect to dashboard with an error
        if ($request->user()?->role !== $role) {
            return redirect()->route('dashboard')
                ->with('error', 'ليس لديك صلاحية الوصول لهذه الصفحة.');
        }

        return $next($request);
    }
}
