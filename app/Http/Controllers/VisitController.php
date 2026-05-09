<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\Index;
use Illuminate\Http\RedirectResponse;

class VisitController extends Controller
{
    public function __invoke(string $slug): RedirectResponse
    {
        $index = Index::where('slug', $slug)->firstOrFail();

        Click::create([
            'index_id' => $index->id,
            'clicked_at' => now(),
        ]);

        return redirect()->away($index->url);
    }
}
