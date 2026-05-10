<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogPageView
{
    private const BOT_SIGNATURES = [
        'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget',
        'facebookexternalhit', 'Twitterbot', 'LinkedInBot',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request, $response)) {
            PageView::create([
                'path' => $request->path() === '/' ? '/' : '/'.$request->path(),
                'referrer' => $this->cleanReferrer($request->header('referer')),
                'visited_at' => now(),
            ]);
        }

        return $response;
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if (!$request->isMethod('GET')) {
            return false;
        }

        if ($request->is('admin', 'admin/*')) {
            return false;
        }

        $ua = strtolower($request->userAgent() ?? '');
        foreach (self::BOT_SIGNATURES as $sig) {
            if (str_contains($ua, strtolower($sig))) {
                return false;
            }
        }

        return true;
    }

    private function cleanReferrer(?string $referrer): ?string
    {
        if (!$referrer) {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);

        // drop self-referrals
        if ($host && str_contains($host, 'theindex.fyi')) {
            return null;
        }

        return $host ?: null;
    }
}
