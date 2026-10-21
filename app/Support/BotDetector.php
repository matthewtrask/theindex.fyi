<?php

namespace App\Support;

use Illuminate\Http\Request;

class BotDetector
{
    private const SIGNATURES = [
        'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget',
        'facebookexternalhit', 'twitterbot', 'linkedinbot',
    ];

    public static function isBot(Request $request): bool
    {
        $ua = strtolower($request->userAgent() ?? '');

        foreach (self::SIGNATURES as $signature) {
            if (str_contains($ua, $signature)) {
                return true;
            }
        }

        return false;
    }
}
