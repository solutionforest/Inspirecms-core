<?php

namespace SolutionForest\InspireCmsApi\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ContentCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     */
    public $collects = ContentResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => $this->getMeta(),
            'links' => $this->getLinks($request),
        ];
    }

    /**
     * Get meta information for the response.
     */
    protected function getMeta(): array
    {
        if (! $this->resource instanceof \Illuminate\Pagination\AbstractPaginator) {
            return [
                'total' => $this->collection->count(),
            ];
        }

        return [
            'current_page' => $this->resource->currentPage(),
            'from' => $this->resource->firstItem(),
            'last_page' => $this->resource->lastPage(),
            'per_page' => $this->resource->perPage(),
            'to' => $this->resource->lastItem(),
            'total' => $this->resource->total(),
        ];
    }

    /**
     * Get pagination links.
     */
    protected function getLinks(Request $request): array
    {
        if (! $this->resource instanceof \Illuminate\Pagination\AbstractPaginator) {
            return [];
        }

        $links = [
            'first' => $this->resource->url(1),
            'last' => $this->resource->url($this->resource->lastPage()),
            'prev' => $this->resource->previousPageUrl(),
            'next' => $this->resource->nextPageUrl(),
        ];

        return array_filter($links);
    }
}
