<?php

namespace App\Http\Resources\Api;

use App\Models\Index;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Index */
class IndexResource extends JsonResource
{
    public static $wrap = 'data';

    public function toArray(Request $request): array
    {
        return [
            'type' => 'indexes',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
                'slug' => $this->slug,
                'url' => $this->url,
                'description' => $this->description,
                'category' => $this->category->value,
                'language' => $this->language,
                'accepts_submissions' => $this->accepts_submissions,
                'last_checked_at' => $this->last_checked_at?->toIso8601String(),
            ],
            'links' => [
                'self' => route('api.indexes.show', $this->slug),
            ],
        ];
    }

    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->header('Content-Type', 'application/vnd.api+json');
    }
}
