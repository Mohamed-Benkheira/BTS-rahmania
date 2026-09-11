<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->employee === null || ! $user->hasRole('employee')) {
            return $request->expectsJson()
                ? abort(403, 'The employee portal requires a linked employee profile.')
                : redirect()->route('home');
        }

        return $next($request);
    }
}
