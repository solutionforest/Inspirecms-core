<?php

namespace SolutionForest\InspireCmsApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('inspirecms-api.enabled', true)) {
            return response()->json([
                'error' => 'API is disabled',
                'message' => 'The API functionality has been disabled on this server.',
            ], 503);
        }

        return $next($request);
    }
}
