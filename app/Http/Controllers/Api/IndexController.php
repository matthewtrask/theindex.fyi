<?php

namespace App\Http\Controllers\Api;

use App\Enums\IndexStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\IndexCollection;
use App\Http\Resources\Api\IndexResource;
use App\Models\Index;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    private const DEFAULT_PAGE_SIZE = 25;

    private const MAX_PAGE_SIZE = 100;

    public function index(Request $request): IndexCollection
    {
        $query = Index::where('status', IndexStatus::Active);

        $category = $request->input('filter.category');
        if (is_string($category) && $category !== '') {
            $query->where('category', $category);
        }

        $language = $request->input('filter.language');
        if (is_string($language) && $language !== '') {
            $query->where('language', $language);
        }

        // out-of-range sizes fall back to the default; a negative size would otherwise drop the LIMIT
        $pageSize = $request->integer('page.size', self::DEFAULT_PAGE_SIZE);
        $pageSize = $pageSize < 1 ? self::DEFAULT_PAGE_SIZE : min($pageSize, self::MAX_PAGE_SIZE);
        $page = max($request->integer('page.number', 1), 1);

        $indexes = $query->orderBy('name')->paginate($pageSize, ['*'], 'page', $page);

        return new IndexCollection($indexes);
    }

    public function show(string $slug): IndexResource
    {
        $index = Index::where('slug', $slug)
            ->where('status', IndexStatus::Active)
            ->firstOrFail();

        return new IndexResource($index);
    }
}
