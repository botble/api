<?php

namespace Botble\Api\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForceJsonResponseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        if ($request->bearerToken()) {
            $user = Auth::guard('sanctum')->user();
            
            if ($user && $user instanceof Authenticatable) {
                Auth::setUser($user);
            }
        }

        return $next($request);
    }
}
