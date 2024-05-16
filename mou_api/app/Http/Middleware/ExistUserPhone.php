<?php

namespace App\Http\Middleware;

use Closure;

class ExistUserPhone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $userFirebase = $request->user();

        if (! $userFirebase || ! $userFirebase->user) {
            return response()->json(['message' => 'Unauthenticated - User Phone does not exist'], 402);
        }

        return $next($request);
    }
}
