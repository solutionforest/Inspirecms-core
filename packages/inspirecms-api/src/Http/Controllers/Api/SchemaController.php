<?php

namespace SolutionForest\InspireCmsApi\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SolutionForest\InspireCmsApi\Http\Resources\DocumentTypeResource;
use SolutionForest\InspireCmsApi\Services\ApiRouteGenerator;
use SolutionForest\InspireCmsApi\Services\ApiSettingsService;

class SchemaController extends Controller
{
    public function __construct(
        protected ApiRouteGenerator $routeGenerator,
        protected ApiSettingsService $apiSettingsService
    ) {}

    /**
     * Get API schema information.
     *
     * GET /api/v1/schema
     */
    public function index(Request $request): JsonResponse
    {
        $documentTypes = $this->routeGenerator->getApiEnabledDocumentTypes();

        $types = $documentTypes->map(function ($documentType) {
            return new DocumentTypeResource($documentType);
        });

        return response()->json([
            'api' => [
                'version' => config('inspirecms-api.version', 'v1'),
                'prefix' => config('inspirecms-api.prefix', 'api'),
            ],
            'types' => $types,
            'authentication' => [
                'methods' => [
                    'bearer_token' => [
                        'header' => 'Authorization',
                        'format' => 'Bearer {token}',
                    ],
                    'api_key' => [
                        'header' => config('inspirecms-api.auth.api_key_header', 'X-API-Key'),
                        'format' => '{api_key}',
                    ],
                ],
            ],
            'rate_limiting' => [
                'enabled' => config('inspirecms-api.rate_limiting.enabled', true),
                'public_limit' => config('inspirecms-api.rate_limiting.public', 60) . ' requests/minute',
                'authenticated_limit' => config('inspirecms-api.rate_limiting.authenticated', 300) . ' requests/minute',
            ],
        ]);
    }

    /**
     * Get schema for a specific content type.
     *
     * GET /api/v1/schema/{type}
     */
    public function show(Request $request, string $type): JsonResponse
    {
        $documentType = $this->routeGenerator->findDocumentTypeBySlug($type);

        if (! $documentType) {
            return response()->json([
                'error' => 'Not Found',
                'message' => "Content type '{$type}' not found.",
            ], 404);
        }

        if (! $this->apiSettingsService->isEnabled($documentType)) {
            return response()->json([
                'error' => 'Not Found',
                'message' => "Content type '{$type}' is not available via API.",
            ], 404);
        }

        return response()->json([
            'data' => new DocumentTypeResource($documentType),
        ]);
    }
}
