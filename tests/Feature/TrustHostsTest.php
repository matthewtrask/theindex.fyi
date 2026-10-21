<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Middleware\TrustHosts;
use Illuminate\Http\Request;
use Tests\TestCase;

class TrustHostsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://theindex.fyi']);

        // TrustHosts is a no-op while running unit tests, so force it on
        $this->app->bind(TrustHosts::class, fn ($app) => new class($app) extends TrustHosts
        {
            protected function shouldSpecifyTrustedHosts()
            {
                return true;
            }
        });
    }

    protected function tearDown(): void
    {
        Request::setTrustedHosts([]);

        parent::tearDown();
    }

    public function test_allows_the_application_host(): void
    {
        $this->get('https://theindex.fyi/')->assertOk();
    }

    public function test_allows_subdomains_of_the_application_host(): void
    {
        $this->get('https://www.theindex.fyi/')->assertOk();
    }

    public function test_rejects_an_untrusted_host(): void
    {
        $this->get('https://evil.example/')->assertBadRequest();
    }

    public function test_rejects_a_host_that_only_starts_with_the_application_host(): void
    {
        $this->get('https://theindex.fyi.evil.example/')->assertBadRequest();
    }

    public function test_password_reset_links_cannot_be_poisoned_with_the_host_header(): void
    {
        $this->post('https://evil.example/forgot-password', ['email' => 'admin@example.com'])
            ->assertBadRequest();
    }
}
