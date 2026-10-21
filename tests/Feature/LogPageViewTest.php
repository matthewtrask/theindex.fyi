<?php

namespace Tests\Feature;

use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LogPageViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_home_page_path(): void
    {
        $this->get('/')->assertOk();

        $this->assertSame(['/'], PageView::pluck('path')->all());
    }

    public function test_logs_route_template_instead_of_password_reset_token(): void
    {
        $this->get('/reset-password/secret-token-123?email=test@example.com')->assertOk();

        $this->assertSame(['/reset-password/{token}'], PageView::pluck('path')->all());
        $this->assertDatabaseMissing('page_views', ['path' => '/reset-password/secret-token-123']);
    }

    public function test_stores_the_referrer_hostname(): void
    {
        $this->get('/', ['Referer' => 'https://Kagi.com/search?q=small+web'])->assertOk();

        $this->assertSame('kagi.com', PageView::sole()->referrer);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function selfReferrers(): array
    {
        return [
            'apex' => ['https://theindex.fyi/about'],
            'www' => ['https://www.theindex.fyi/'],
        ];
    }

    #[DataProvider('selfReferrers')]
    public function test_drops_self_referrals(string $referrer): void
    {
        config(['app.url' => 'https://theindex.fyi']);

        $this->get('/', ['Referer' => $referrer])->assertOk();

        $this->assertNull(PageView::sole()->referrer);
    }

    public function test_keeps_lookalike_domains_that_are_not_self_referrals(): void
    {
        config(['app.url' => 'https://theindex.fyi']);

        $this->get('/', ['Referer' => 'https://theindex.fyi.example.com/'])->assertOk();

        $this->assertSame('theindex.fyi.example.com', PageView::sole()->referrer);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function junkReferrers(): array
    {
        return [
            'html' => ['http://<img src=x onerror=alert(1)>/'],
            'malformed ipv6' => ['http://[::1'],
            'no dot' => ['http://localhost/'],
            'too long' => ['http://' . implode('.', array_fill(0, 5, str_repeat('a', 60))) . '.com/'],
            'not a url' => ['just some text'],
        ];
    }

    #[DataProvider('junkReferrers')]
    public function test_discards_invalid_referrers(string $referrer): void
    {
        $this->get('/', ['Referer' => $referrer])->assertOk();

        $this->assertNull(PageView::sole()->referrer);
    }
}
