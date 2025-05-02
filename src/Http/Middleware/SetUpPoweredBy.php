<?php

namespace SolutionForest\InspireCms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\InspireCmsConfig;

class SetUpPoweredBy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (InspireCmsConfig::get('system.send_powered_by_header', true) && 
			($response instanceof Response || $response instanceof StreamedResponse)
		) {
            $version = InspireCms::version();
            $response->headers->set('X-Powered-By', "InspireCMS/{$version}");
        }

        return $response;
    }
}
