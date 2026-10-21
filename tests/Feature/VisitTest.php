<?php

namespace Tests\Feature;

use App\Models\Click;
use App\Models\Index;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VisitTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 Safari/605.1.15';

    public function test_redirects_to_the_entry_and_records_a_click(): void
    {
        $index = Index::factory()->create(['url' => 'https://example.com']);

        $this->get(route('visit', $index->slug), ['User-Agent' => self::BROWSER])
            ->assertRedirect('https://example.com');

        $this->assertSame(1, Click::where('index_id', $index->id)->count());
    }

    public function test_unknown_slugs_404(): void
    {
        $this->get('/visit/does-not-exist')->assertNotFound();
    }

    public function test_repeat_visits_from_the_same_visitor_count_once_per_hour(): void
    {
        $index = Index::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->get(route('visit', $index->slug), ['User-Agent' => self::BROWSER])->assertRedirect();
        }

        $this->assertSame(1, Click::count());

        $this->travel(61)->minutes();
        $this->get(route('visit', $index->slug), ['User-Agent' => self::BROWSER]);

        $this->assertSame(2, Click::count());
    }

    public function test_different_visitors_are_counted_separately(): void
    {
        $index = Index::factory()->create();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1'])->get(route('visit', $index->slug), ['User-Agent' => self::BROWSER]);
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.2'])->get(route('visit', $index->slug), ['User-Agent' => self::BROWSER]);

        $this->assertSame(2, Click::count());
    }

    public function test_visits_to_different_entries_are_counted_separately(): void
    {
        [$a, $b] = Index::factory()->count(2)->create();

        $this->get(route('visit', $a->slug), ['User-Agent' => self::BROWSER]);
        $this->get(route('visit', $b->slug), ['User-Agent' => self::BROWSER]);

        $this->assertSame(2, Click::count());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function bots(): array
    {
        return [
            'googlebot' => ['Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
            'curl' => ['curl/8.4.0'],
            'twitter' => ['Twitterbot/1.0'],
        ];
    }

    #[DataProvider('bots')]
    public function test_bots_are_redirected_but_not_counted(string $userAgent): void
    {
        $index = Index::factory()->create(['url' => 'https://example.com']);

        $this->get(route('visit', $index->slug), ['User-Agent' => $userAgent])
            ->assertRedirect('https://example.com');

        $this->assertSame(0, Click::count());
    }

    public function test_robots_txt_disallows_visit_links(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /visit/');
    }
}
