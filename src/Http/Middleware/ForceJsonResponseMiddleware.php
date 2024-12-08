<?php

namespace Botble\Api\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForceJsonResponseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        if ($request->bearerToken()) {
            Auth::setUser(Auth::guard('sanctum')->user());
        }

        return $next($request);
    }
}
