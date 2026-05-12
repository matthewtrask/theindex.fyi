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
    public function index(Request $request): IndexCollection
    {
        $query = Index::where('status', IndexStatus::Active);

        if ($category = $request->input('filter.category')) {
            $query->where('category', $category);
        }

        if ($language = $request->input('filter.language')) {
            $query->where('language', $language);
        }

        $pageSize = min((int) $request->input('page.size', 25), 100);
        $page = max((int) $request->input('page.number', 1), 1);

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
