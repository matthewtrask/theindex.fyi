<?php

namespace App\Http\Resources\Api;

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
                'self' => $this->resource->url($this->resource->currentPage()),
                'first' => $this->resource->url(1),
                'last' => $this->resource->url($this->resource->lastPage()),
                'prev' => $this->resource->previousPageUrl(),
                'next' => $this->resource->nextPageUrl(),
            ],
        ];
    }

    public function toResponse($request): \Illuminate\Http\JsonResponse
    {
        return response()
            ->json($this->toArray($request))
            ->header('Content-Type', 'application/vnd.api+json');
    }
}
