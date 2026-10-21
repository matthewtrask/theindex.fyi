<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\Index;
use App\Support\BotDetector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class VisitController extends Controller
{
    public function __invoke(Request $request, string $slug): RedirectResponse
    {
        $index = Index::where('slug', $slug)->firstOrFail();

        if ($this->shouldCount($request, $index)) {
            Click::create([
                'index_id' => $index->id,
                'clicked_at' => now(),
            ]);
        }

        return redirect()->away($index->url);
    }

    // count one click per visitor per entry per hour, and never count bots
    private function shouldCount(Request $request, Index $index): bool
    {
        if (BotDetector::isBot($request)) {
            return false;
        }

        $key = 'click:' . $index->id . ':' . hash('sha256', (string) $request->ip());

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return false;
        }

        RateLimiter::hit($key, 3600);

        return true;
    }
}
