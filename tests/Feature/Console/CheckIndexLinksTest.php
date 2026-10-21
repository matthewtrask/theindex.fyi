<?php

namespace Tests\Feature\Console;

use App\Enums\IndexStatus;
use App\Models\Index;
use App\Support\HostResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CheckIndexLinksTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, list<string>> */
    private array $dns = [];

    protected function setUp(): void
    {
        parent::setUp();

        // every host resolves to a public address unless a test says otherwise
        $this->app->instance(HostResolver::class, new class($this->dns) extends HostResolver
        {
            /** @param  array<string, list<string>>  $dns */
            public function __construct(private array &$dns) {}

            public function resolve(string $host): array
            {
                return $this->dns[$host] ?? (filter_var($host, FILTER_VALIDATE_IP) ? [$host] : ['93.184.215.14']);
            }
        });
    }

    public function test_marks_a_reachable_site_active(): void
    {
        $index = Index::factory()->inactive()->create(['url' => 'https://good.example']);
        Http::fake(['good.example' => Http::response('<h1>A small web index</h1>')]);

        $this->artisan('indexes:check-links')->assertSuccessful();

        $this->assertSame(IndexStatus::Active, $index->fresh()->status);
    }

    public function test_marks_an_error_response_inactive(): void
    {
        $index = Index::factory()->create(['url' => 'https://broken.example']);
        Http::fake(['broken.example' => Http::response('', 500)]);

        $this->artisan('indexes:check-links')->assertSuccessful();

        $this->assertSame(IndexStatus::Inactive, $index->fresh()->status);
    }

    public function test_marks_a_parked_domain_inactive(): void
    {
        $index = Index::factory()->create(['url' => 'https://parked.example']);
        Http::fake(['parked.example' => Http::response('<p>This Domain Is For Sale</p>')]);

        $this->artisan('indexes:check-links')->assertSuccessful();

        $this->assertSame(IndexStatus::Inactive, $index->fresh()->status);
    }

    public function test_marks_an_unreachable_site_dead(): void
    {
        $index = Index::factory()->create(['url' => 'https://gone.example']);
        Http::fake(['gone.example' => fn () => throw new ConnectionException('Could not resolve host')]);

        $this->artisan('indexes:check-links')->assertSuccessful();

        $this->assertSame(IndexStatus::Dead, $index->fresh()->status);
    }

    public function test_sends_the_link_checker_user_agent(): void
    {
        Index::factory()->create(['url' => 'https://good.example']);
        Http::fake(['good.example' => Http::response('ok')]);

        $this->artisan('indexes:check-links')->assertSuccessful();

        Http::assertSent(fn ($request) => str_contains($request->header('User-Agent')[0], 'theindex.fyi/linkchecker'));
    }

    public function test_checks_a_single_index_by_id(): void
    {
        $checked = Index::factory()->create(['url' => 'https://good.example']);
        $skipped = Index::factory()->create(['url' => 'https://other.example', 'last_checked_at' => null]);
        Http::fake(['good.example' => Http::response('ok')]);

        $this->artisan('indexes:check-links', ['--id' => $checked->id])->assertSuccessful();

        Http::assertSentCount(1);
        $this->assertNull($skipped->fresh()->last_checked_at);
    }

    public function test_skips_entries_an_admin_has_delisted(): void
    {
        $index = Index::factory()->inactive()->create(['url' => 'https://good.example', 'status_locked' => true]);
        Http::fake(['good.example' => Http::response('ok')]);

        $this->artisan('indexes:check-links')->assertSuccessful();

        Http::assertNothingSent();
        $this->assertSame(IndexStatus::Inactive, $index->fresh()->status);
    }

    public function test_follows_redirects_to_public_hosts(): void
    {
        $index = Index::factory()->inactive()->create(['url' => 'http://old.example']);
        Http::fake([
            'old.example*' => Http::response('', 301, ['Location' => 'https://new.example/home']),
            'new.example*' => Http::response('ok'),
        ]);

        $this->artisan('indexes:check-links')->assertSuccessful();

        $this->assertSame(IndexStatus::Active, $index->fresh()->status);
        Http::assertSent(fn ($request) => $request->url() === 'https://new.example/home');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function nonPublicAddresses(): array
    {
        return [
            'loopback' => ['127.0.0.1'],
            'cloud metadata' => ['169.254.169.254'],
            'private' => ['10.0.0.5'],
            'carrier-grade nat' => ['100.64.0.1'],
            'unspecified' => ['0.0.0.0'],
            'ipv6 loopback' => ['::1'],
            'ipv6 unique local' => ['fd00::1'],
        ];
    }

    #[DataProvider('nonPublicAddresses')]
    public function test_refuses_hosts_that_resolve_to_non_public_addresses(string $address): void
    {
        $this->dns['internal.example'] = [$address];
        $index = Index::factory()->create(['url' => 'https://internal.example']);
        Http::fake();

        $this->artisan('indexes:check-links')->assertSuccessful();

        Http::assertNothingSent();
        $this->assertSame(IndexStatus::Dead, $index->fresh()->status);
    }

    public function test_refuses_hosts_with_any_non_public_address(): void
    {
        $this->dns['mixed.example'] = ['93.184.215.14', '127.0.0.1'];
        Index::factory()->create(['url' => 'https://mixed.example']);
        Http::fake();

        $this->artisan('indexes:check-links')->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_refuses_ip_literal_urls_for_private_addresses(): void
    {
        $index = Index::factory()->create(['url' => 'http://169.254.169.254/latest/meta-data/']);
        Http::fake();

        $this->artisan('indexes:check-links')->assertSuccessful();

        Http::assertNothingSent();
        $this->assertSame(IndexStatus::Dead, $index->fresh()->status);
    }

    public function test_refuses_redirects_to_non_public_addresses(): void
    {
        $this->dns['internal.example'] = ['10.0.0.5'];
        $index = Index::factory()->create(['url' => 'https://good.example']);
        Http::fake([
            'good.example*' => Http::response('', 302, ['Location' => 'http://internal.example/admin']),
            '*' => Http::response('internal secrets'),
        ]);

        $this->artisan('indexes:check-links')->assertSuccessful();

        Http::assertSentCount(1);
        $this->assertSame(IndexStatus::Dead, $index->fresh()->status);
    }

    public function test_gives_up_after_too_many_redirects(): void
    {
        $index = Index::factory()->create(['url' => 'https://loop.example']);
        Http::fake(['loop.example*' => Http::response('', 302, ['Location' => 'https://loop.example/again'])]);

        $this->artisan('indexes:check-links')->assertSuccessful();

        Http::assertSentCount(6);
        $this->assertSame(IndexStatus::Dead, $index->fresh()->status);
    }

    public function test_only_reads_the_start_of_large_responses(): void
    {
        $index = Index::factory()->create(['url' => 'https://huge.example']);
        Http::fake(['huge.example' => Http::response(str_repeat('a', 1024 * 1024) . 'domain for sale')]);

        $this->artisan('indexes:check-links')->assertSuccessful();

        // the parking signal is past the read limit, so it is never seen
        $this->assertSame(IndexStatus::Active, $index->fresh()->status);
    }
}
