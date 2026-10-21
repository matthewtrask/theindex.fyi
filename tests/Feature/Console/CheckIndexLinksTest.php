<?php

namespace Tests\Feature\Console;

use App\Enums\IndexStatus;
use App\Models\Index;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckIndexLinksTest extends TestCase
{
    use RefreshDatabase;

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
}
