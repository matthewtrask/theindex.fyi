<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Click;
use App\Models\Index;
use App\Models\PageView;
use Illuminate\View\View;

class StatsController extends Controller
{
    public function __invoke(): View
    {
        $entries = Index::withCount('clicks')
            ->orderByDesc('clicks_count')
            ->get();

        $totalClicks = Click::count();

        $viewsToday = PageView::whereDate('visited_at', today())->count();
        $viewsWeek  = PageView::where('visited_at', '>=', now()->subDays(7))->count();
        $viewsTotal = PageView::count();

        $topPaths = PageView::selectRaw('path, count(*) as hits')
            ->groupBy('path')
            ->orderByDesc('hits')
            ->limit(10)
            ->get();

        $topReferrers = PageView::selectRaw('referrer, count(*) as hits')
            ->whereNotNull('referrer')
            ->groupBy('referrer')
            ->orderByDesc('hits')
            ->limit(10)
            ->get();

        return view('admin.stats', compact(
            'entries', 'totalClicks',
            'viewsToday', 'viewsWeek', 'viewsTotal',
            'topPaths', 'topReferrers',
        ));
    }
}
