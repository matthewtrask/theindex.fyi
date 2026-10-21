<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function publicPages(): array
    {
        return [
            'home' => ['/'],
            'login' => ['/login'],
            'api' => ['/api/indexes'],
            'api docs' => ['/api/docs'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_responses_include_security_headers(string $uri): void
    {
        $this->get($uri)
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'");
    }

    public function test_admin_pages_cannot_be_framed(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/indexes')
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_redirects_include_security_headers(): void
    {
        $this->get('/admin/stats')
            ->assertRedirect()
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_hsts_is_sent_over_https(): void
    {
        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }

    public function test_hsts_is_not_sent_over_http(): void
    {
        $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');
    }
}
