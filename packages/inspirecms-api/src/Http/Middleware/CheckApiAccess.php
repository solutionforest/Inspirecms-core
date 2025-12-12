<?php

namespace SolutionForest\InspireCmsApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCmsApi\Services\ApiSettingsService;
use Symfony\Component\HttpFoundation\Response;

class CheckApiAccess
{
    public function __construct(
        protected ApiSettingsService $apiSettingsService
    ) {}

    public function handle(Request $request, Closure $next, string $operation = 'show'): Response
    {
        $documentTypeSlug = $request->route('type');

        if (! $documentTypeSlug) {
            return $next($request);
        }

        // Find the document type
        $documentType = $this->findDocumentType($documentTypeSlug);

        if (! $documentType) {
            return response()->json([
                'error' => 'Not Found',
                'message' => "Content type '{$documentTypeSlug}' not found.",
            ], 404);
        }

        // Check if API is enabled for this document type
        $apiSettings = $this->apiSettingsService->getSettings($documentType);

        if (! $apiSettings['enabled']) {
            return response()->json([
                'error' => 'Not Found',
                'message' => "Content type '{$documentTypeSlug}' is not available via API.",
            ], 404);
        }

        // Check if operation is allowed
        if (! in_array($operation, $apiSettings['allowed_operations'])) {
            return response()->json([
                'error' => 'Method Not Allowed',
                'message' => "Operation '{$operation}' is not allowed for this content type.",
            ], 405);
        }

        // Check authentication requirements
        $isWriteOperation = in_array($operation, ['store', 'update', 'destroy']);
        $isAuthenticated = $request->attributes->has('api_token');

        if ($isWriteOperation && ! $apiSettings['public_write'] && ! $isAuthenticated) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Authentication required for write operations.',
            ], 401);
        }

        if (! $isWriteOperation && ! $apiSettings['public_read'] && ! $isAuthenticated) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Authentication required to access this content type.',
            ], 401);
        }

        // Store the document type on the request for the controller
        $request->attributes->set('document_type', $documentType);
        $request->attributes->set('api_settings', $apiSettings);

        return $next($request);
    }

    protected function findDocumentType(string $slug)
    {
        $documentTypeClass = InspireCmsConfig::getDocumentTypeModelClass();

        // Try to find by API slug first, then by regular slug
        return $documentTypeClass::query()
            ->where(function ($query) use ($slug) {
                $query->whereJsonContains('api_settings->slug', $slug)
                    ->orWhere('slug', $slug);
            })
            ->first();
    }
}
