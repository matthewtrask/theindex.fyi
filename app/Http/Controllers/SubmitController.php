<?php

namespace App\Http\Controllers;

use App\Enums\Category;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmitController extends Controller
{
    public function create(): View
    {
        $categories = Category::cases();

        return view('submit', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255'],
            'description' => ['required', 'string', 'max:500'],
            'category' => ['nullable', 'string'],
            'submitted_by_email' => ['nullable', 'email', 'max:255'],
        ]);

        Submission::create($validated);

        return redirect()->route('submit')->with('success', 'Thanks — your submission is in the queue.');
    }
}
