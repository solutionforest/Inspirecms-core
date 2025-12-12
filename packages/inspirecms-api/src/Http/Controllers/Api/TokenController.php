<?php

namespace SolutionForest\InspireCmsApi\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCmsApi\Models\ApiToken;

class TokenController extends Controller
{
    /**
     * Create a new API token.
     *
     * POST /api/v1/auth/token
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'name' => 'sometimes|string|max:255',
            'abilities' => 'sometimes|array',
            'expires_in_days' => 'sometimes|integer|min:1|max:365',
        ]);

        $userClass = InspireCmsConfig::getUserModelClass();
        $guardName = AuthHelper::guardName();

        // Find user by email
        $user = $userClass::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Create the token
        $tokenData = ApiToken::createToken(
            name: $validated['name'] ?? 'API Token',
            userId: $user->getKey(),
            abilities: $validated['abilities'] ?? ['*'],
            expiryDays: $validated['expires_in_days'] ?? null
        );

        return response()->json([
            'message' => 'Token created successfully.',
            'data' => [
                'token' => $tokenData['plain_token'],
                'type' => 'Bearer',
                'expires_at' => $tokenData['token']->expires_at?->toIso8601String(),
                'abilities' => $tokenData['token']->abilities,
            ],
        ], 201);
    }

    /**
     * Revoke the current API token.
     *
     * DELETE /api/v1/auth/token
     */
    public function destroy(Request $request): JsonResponse
    {
        $apiToken = $request->attributes->get('api_token');

        if (! $apiToken) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'No valid token to revoke.',
            ], 401);
        }

        $apiToken->revoke();

        return response()->json([
            'message' => 'Token revoked successfully.',
        ]);
    }

    /**
     * List all tokens for the authenticated user.
     *
     * GET /api/v1/auth/tokens
     */
    public function index(Request $request): JsonResponse
    {
        $apiToken = $request->attributes->get('api_token');

        if (! $apiToken || ! $apiToken->user_id) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Authentication required.',
            ], 401);
        }

        $tokens = ApiToken::where('user_id', $apiToken->user_id)
            ->valid()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'expires_at' => $token->expires_at?->toIso8601String(),
                'created_at' => $token->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'data' => $tokens,
        ]);
    }

    /**
     * Revoke a specific token by ID.
     *
     * DELETE /api/v1/auth/tokens/{id}
     */
    public function revokeById(Request $request, int $id): JsonResponse
    {
        $currentToken = $request->attributes->get('api_token');

        if (! $currentToken || ! $currentToken->user_id) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Authentication required.',
            ], 401);
        }

        $tokenToRevoke = ApiToken::where('id', $id)
            ->where('user_id', $currentToken->user_id)
            ->first();

        if (! $tokenToRevoke) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Token not found.',
            ], 404);
        }

        $tokenToRevoke->revoke();

        return response()->json([
            'message' => 'Token revoked successfully.',
        ]);
    }
}
