<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDivision
{
    public function handle(
        Request $request,
        Closure $next,
        ...$divisions
    ): Response {

        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        /*
        |--------------------------------------------------------------------------
        | BYPASS USER
        |--------------------------------------------------------------------------
        */

        $bypassUsers = config('access.bypass_user_ids');

        if (in_array($user->id, $bypassUsers)) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK DIVISION
        |--------------------------------------------------------------------------
        */

        $userDivision = $user?->employee?->division;

        if (!in_array($userDivision, $divisions)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
