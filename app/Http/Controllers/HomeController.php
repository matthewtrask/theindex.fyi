<?php

namespace App\Http\Controllers;

use App\Enums\Category;
use App\Models\Index;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $active = collect(Category::cases())
            ->first(fn (Category $c) => $c->value === $request->query('category'));

        $categories = Category::cases();

        $grouped = collect(Category::cases())
            ->when($active, fn ($col) => $col->filter(fn (Category $c) => $c === $active))
            ->mapWithKeys(function (Category $category) {
                return [
                    $category->value => [
                        'category' => $category,
                        'entries' => Index::where('category', $category->value)
                            ->orderBy('name')
                            ->get(),
                    ],
                ];
            });

        return view('home', compact('grouped', 'categories', 'active'));
    }
}
