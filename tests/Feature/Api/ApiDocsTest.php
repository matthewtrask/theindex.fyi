<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDocsTest extends TestCase
{
    use RefreshDatabase;

    public function test_docs_page_loads_scalar_with_subresource_integrity(): void
    {
        $response = $this->get('/api/docs')->assertOk();

        $response->assertSee('src="' . config('scalar.cdn') . '"', false);
        $response->assertSee('integrity="' . config('scalar.cdn_integrity') . '"', false);
        $response->assertSee('crossorigin="anonymous"', false);
    }

    public function test_scalar_is_pinned_to_an_exact_version(): void
    {
        $this->assertMatchesRegularExpression('#@scalar/api-reference@\d+\.\d+\.\d+/#', config('scalar.cdn'));
        $this->assertMatchesRegularExpression('#^sha384-[A-Za-z0-9+/]{64}$#', config('scalar.cdn_integrity'));
    }

    public function test_openapi_spec_is_served(): void
    {
        $this->get('/api/openapi.yaml')->assertOk();
    }
}
