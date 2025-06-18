<?php

namespace Botble\Api\Http\Middleware;

use Botble\Api\Facades\ApiHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! ApiHelper::hasApiKey()) {
            return $next($request);
        }

        $apiKey = ApiHelper::getApiKey();
        $requestApiKey = $request->header('X-API-KEY');

        if (! $requestApiKey || $requestApiKey !== $apiKey) {
            return response()->json([
                'message' => 'Invalid or missing API key. Please provide a valid X-API-KEY header.',
                'error' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
