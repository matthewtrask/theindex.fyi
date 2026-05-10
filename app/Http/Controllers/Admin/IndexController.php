<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Category;
use App\Enums\IndexStatus;
use App\Http\Controllers\Controller;
use App\Models\Index;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{
    public function index(): View
    {
        $entries = Index::orderBy('category')->orderBy('name')->paginate(50);

        return view('admin.indexes.index', compact('entries'));
    }

    public function edit(Index $index): View
    {
        $categories = Category::cases();
        $statuses = IndexStatus::cases();

        return view('admin.indexes.edit', compact('index', 'categories', 'statuses'));
    }

    public function update(Request $request, Index $index): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255'],
            'description' => ['required', 'string', 'max:500'],
            'category' => ['required', 'string'],
            'status' => ['required', 'string'],
            'accepts_submissions' => ['boolean'],
        ]);

        $validated['accepts_submissions'] = $request->boolean('accepts_submissions');

        $index->update($validated);

        return redirect()->route('admin.indexes.index')
            ->with('success', "Entry updated.");
    }

    public function destroy(Index $index): RedirectResponse
    {
        $index->delete();

        return redirect()->route('admin.indexes.index')
            ->with('success', "Entry deleted.");
    }
}
