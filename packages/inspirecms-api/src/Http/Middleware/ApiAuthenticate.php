<?php

namespace SolutionForest\InspireCmsApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use SolutionForest\InspireCmsApi\Models\ApiToken;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    public function handle(Request $request, Closure $next, string $ability = '*'): Response
    {
        $token = $this->getTokenFromRequest($request);

        if (! $token) {
            return $this->unauthorized('No API token provided');
        }

        $apiToken = ApiToken::findByPlainToken($token);

        if (! $apiToken) {
            return $this->unauthorized('Invalid API token');
        }

        if ($apiToken->isExpired()) {
            return $this->unauthorized('API token has expired');
        }

        if ($ability !== '*' && ! $apiToken->hasAbility($ability)) {
            return $this->forbidden('Token does not have the required ability: ' . $ability);
        }

        // Update last used timestamp
        $apiToken->touchLastUsed();

        // Store the token and user on the request for later use
        $request->attributes->set('api_token', $apiToken);

        if ($apiToken->user) {
            $request->setUserResolver(fn () => $apiToken->user);
        }

        return $next($request);
    }

    protected function getTokenFromRequest(Request $request): ?string
    {
        // Try Bearer token first
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            return $bearerToken;
        }

        // Try API key header
        $apiKeyHeader = config('inspirecms-api.auth.api_key_header', 'X-API-Key');
        $apiKey = $request->header($apiKeyHeader);
        if ($apiKey) {
            return $apiKey;
        }

        // Try query parameter (less secure, but useful for testing)
        return $request->query('api_token');
    }

    protected function unauthorized(string $message): Response
    {
        return response()->json([
            'error' => 'Unauthorized',
            'message' => $message,
        ], 401);
    }

    protected function forbidden(string $message): Response
    {
        return response()->json([
            'error' => 'Forbidden',
            'message' => $message,
        ], 403);
    }
}
