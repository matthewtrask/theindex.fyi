<?php

namespace Tests\Feature;

use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
