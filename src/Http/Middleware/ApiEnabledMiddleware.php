<?php

namespace Botble\Api\Http\Middleware;

use Botble\Api\Facades\ApiHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiEnabledMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! ApiHelper::enabled()) {
            return response()->json([
                'error' => true,
                'message' => 'API is currently disabled. Please contact the administrator to enable API access.',
                'data' => null,
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $next($request);
    }
}
