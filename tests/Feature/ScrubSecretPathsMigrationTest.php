<?php

namespace Tests\Feature;

use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrubSecretPathsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_removes_page_views_containing_secrets_in_the_path(): void
    {
        foreach (['/', '/about', '/reset-password/secret-token', '/email/verify/1/secret-hash', '/reset-password/{token}'] as $path) {
            PageView::create(['path' => $path, 'visited_at' => now()]);
        }

        $migration = require database_path('migrations/2026_10_21_000000_scrub_secret_paths_from_page_views.php');
        $migration->up();

        $this->assertEqualsCanonicalizing(['/', '/about'], PageView::pluck('path')->all());
    }
}
