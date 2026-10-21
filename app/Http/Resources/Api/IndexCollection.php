<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class IndexCollection extends ResourceCollection
{
    public $collects = IndexResource::class;

    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(fn ($resource) => $resource->toArray($request)),
            'meta' => [
                'total' => $this->resource->total(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
            ],
            'links' => [
                'self' => $this->pageUrl($request, $this->resource->currentPage()),
                'first' => $this->pageUrl($request, 1),
                'last' => $this->pageUrl($request, $this->resource->lastPage()),
                'prev' => $this->resource->onFirstPage() ? null : $this->pageUrl($request, $this->resource->currentPage() - 1),
                'next' => $this->resource->hasMorePages() ? $this->pageUrl($request, $this->resource->currentPage() + 1) : null,
            ],
        ];
    }

    /**
     * Build a JSON:API page link that keeps the request's filters and page size.
     * The paginator's own links use ?page=N, which the API doesn't read.
     */
    private function pageUrl(Request $request, int $page): string
    {
        $query = array_merge($request->query(), [
            'page' => ['number' => $page, 'size' => $this->resource->perPage()],
        ]);

        return $request->url() . '?' . http_build_query($query);
    }

    public function toResponse($request): JsonResponse
    {
        return response()
            ->json($this->toArray($request))
            ->header('Content-Type', 'application/vnd.api+json');
    }
}
