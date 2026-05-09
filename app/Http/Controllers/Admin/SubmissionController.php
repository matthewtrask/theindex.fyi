<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function index(): View
    {
        $submissions = Submission::latest()->paginate(25);

        return view('admin.submissions.index', compact('submissions'));
    }

    public function update(Request $request, Submission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,approved,rejected'],
        ]);

        $submission->update($validated);

        return redirect()->route('admin.submissions.index')
            ->with('success', "Submission marked as {$validated['status']}.");
    }
}
