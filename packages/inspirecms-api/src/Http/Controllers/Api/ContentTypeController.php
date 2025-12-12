<?php

namespace SolutionForest\InspireCmsApi\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCmsApi\Http\Resources\ContentCollection;
use SolutionForest\InspireCmsApi\Http\Resources\ContentResource;
use SolutionForest\InspireCmsApi\Services\ApiRouteGenerator;
use SolutionForest\InspireCmsApi\Services\ApiSettingsService;
use SolutionForest\InspireCmsApi\Services\ContentQueryService;

class ContentTypeController extends Controller
{
    public function __construct(
        protected ApiRouteGenerator $routeGenerator,
        protected ContentQueryService $queryService,
        protected ApiSettingsService $apiSettingsService
    ) {}

    /**
     * List all content items for a document type.
     *
     * GET /api/v1/{type}
     */
    public function index(Request $request, string $type): JsonResponse|ContentCollection
    {
        $documentType = $this->resolveDocumentType($type);

        if (! $documentType) {
            return $this->notFound("Content type '{$type}' not found.");
        }

        if (! $this->checkAccess($documentType, 'index', $request)) {
            return $this->unauthorized();
        }

        $query = $this->queryService->buildQuery($documentType, $request);
        $paginated = $this->queryService->paginate($query, $request, $documentType);

        return new ContentCollection($paginated);
    }

    /**
     * Get a single content item by ID.
     *
     * GET /api/v1/{type}/{id}
     */
    public function show(Request $request, string $type, string $id): JsonResponse|ContentResource
    {
        $documentType = $this->resolveDocumentType($type);

        if (! $documentType) {
            return $this->notFound("Content type '{$type}' not found.");
        }

        if (! $this->checkAccess($documentType, 'show', $request)) {
            return $this->unauthorized();
        }

        $content = $this->queryService->findById($documentType, $id, $request);

        if (! $content) {
            return $this->notFound("Content item with ID '{$id}' not found.");
        }

        // Check if content is published (unless authenticated)
        if (! $content->isPublished() && ! $this->isAuthenticated($request)) {
            return $this->notFound("Content item with ID '{$id}' not found.");
        }

        return new ContentResource($content);
    }

    /**
     * Get a single content item by slug.
     *
     * GET /api/v1/{type}/slug/{slug}
     */
    public function showBySlug(Request $request, string $type, string $slug): JsonResponse|ContentResource
    {
        $documentType = $this->resolveDocumentType($type);

        if (! $documentType) {
            return $this->notFound("Content type '{$type}' not found.");
        }

        if (! $this->checkAccess($documentType, 'show', $request)) {
            return $this->unauthorized();
        }

        $content = $this->queryService->findBySlug($documentType, $slug, $request);

        if (! $content) {
            return $this->notFound("Content item with slug '{$slug}' not found.");
        }

        // Check if content is published (unless authenticated)
        if (! $content->isPublished() && ! $this->isAuthenticated($request)) {
            return $this->notFound("Content item with slug '{$slug}' not found.");
        }

        return new ContentResource($content);
    }

    /**
     * Create a new content item.
     *
     * POST /api/v1/{type}
     */
    public function store(Request $request, string $type): JsonResponse|ContentResource
    {
        $documentType = $this->resolveDocumentType($type);

        if (! $documentType) {
            return $this->notFound("Content type '{$type}' not found.");
        }

        if (! $this->checkAccess($documentType, 'store', $request)) {
            return $this->unauthorized('Authentication required for creating content.');
        }

        // Validate required fields
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'status' => 'sometimes|string',
            'attributes' => 'sometimes|array',
            'parent_id' => 'sometimes|nullable|string',
        ]);

        $contentClass = InspireCmsConfig::getContentModelClass();

        // Create the content
        $content = $contentClass::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? \Illuminate\Support\Str::slug($validated['title']),
            'document_type_id' => $documentType->getKey(),
            'parent_id' => $validated['parent_id'] ?? null,
            'status' => $validated['status'] ?? 'draft',
        ]);

        // Handle property data if provided
        if (isset($validated['attributes'])) {
            // Store property data through the content versioning system
            // This depends on how InspireCMS handles property data storage
        }

        $content->load(['documentType']);

        return (new ContentResource($content))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update an existing content item.
     *
     * PUT /api/v1/{type}/{id}
     */
    public function update(Request $request, string $type, string $id): JsonResponse|ContentResource
    {
        $documentType = $this->resolveDocumentType($type);

        if (! $documentType) {
            return $this->notFound("Content type '{$type}' not found.");
        }

        if (! $this->checkAccess($documentType, 'update', $request)) {
            return $this->unauthorized('Authentication required for updating content.');
        }

        $content = $this->queryService->findById($documentType, $id, $request);

        if (! $content) {
            return $this->notFound("Content item with ID '{$id}' not found.");
        }

        // Validate fields
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'status' => 'sometimes|string',
            'attributes' => 'sometimes|array',
            'parent_id' => 'sometimes|nullable|string',
        ]);

        // Update the content
        $content->update(array_filter([
            'title' => $validated['title'] ?? null,
            'slug' => $validated['slug'] ?? null,
            'status' => $validated['status'] ?? null,
            'parent_id' => array_key_exists('parent_id', $validated) ? $validated['parent_id'] : null,
        ], fn ($value) => ! is_null($value)));

        // Handle property data if provided
        if (isset($validated['attributes'])) {
            // Update property data through the content versioning system
        }

        $content->refresh();
        $content->load(['documentType']);

        return new ContentResource($content);
    }

    /**
     * Delete a content item.
     *
     * DELETE /api/v1/{type}/{id}
     */
    public function destroy(Request $request, string $type, string $id): JsonResponse
    {
        $documentType = $this->resolveDocumentType($type);

        if (! $documentType) {
            return $this->notFound("Content type '{$type}' not found.");
        }

        if (! $this->checkAccess($documentType, 'destroy', $request)) {
            return $this->unauthorized('Authentication required for deleting content.');
        }

        $content = $this->queryService->findById($documentType, $id, $request);

        if (! $content) {
            return $this->notFound("Content item with ID '{$id}' not found.");
        }

        $content->delete();

        return response()->json([
            'message' => 'Content deleted successfully.',
        ], 200);
    }

    /**
     * Resolve a document type from its slug.
     */
    protected function resolveDocumentType(string $slug)
    {
        return $this->routeGenerator->findDocumentTypeBySlug($slug);
    }

    /**
     * Check if the request has access to perform an operation.
     */
    protected function checkAccess($documentType, string $operation, Request $request): bool
    {
        $apiSettings = $this->apiSettingsService->getSettings($documentType);

        // Check if API is enabled
        if (! $apiSettings['enabled']) {
            return false;
        }

        // Check if operation is allowed
        if (! in_array($operation, $apiSettings['allowed_operations'])) {
            return false;
        }

        $isAuthenticated = $this->isAuthenticated($request);
        $isWriteOperation = in_array($operation, ['store', 'update', 'destroy']);

        // Write operations require authentication unless public_write is enabled
        if ($isWriteOperation) {
            return $apiSettings['public_write'] || $isAuthenticated;
        }

        // Read operations require authentication unless public_read is enabled
        return $apiSettings['public_read'] || $isAuthenticated;
    }

    /**
     * Check if the request is authenticated.
     */
    protected function isAuthenticated(Request $request): bool
    {
        return $request->attributes->has('api_token');
    }

    /**
     * Return a not found response.
     */
    protected function notFound(string $message): JsonResponse
    {
        return response()->json([
            'error' => 'Not Found',
            'message' => $message,
        ], 404);
    }

    /**
     * Return an unauthorized response.
     */
    protected function unauthorized(string $message = 'Authentication required.'): JsonResponse
    {
        return response()->json([
            'error' => 'Unauthorized',
            'message' => $message,
        ], 401);
    }
}
