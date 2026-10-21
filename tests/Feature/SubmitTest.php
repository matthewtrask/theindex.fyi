<?php

namespace Tests\Feature;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SubmitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function submission(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Example Index',
            'url' => 'https://example.com',
            'description' => 'A small web index',
        ], $overrides);
    }

    public function test_a_submission_is_stored_as_pending(): void
    {
        $this->post(route('submit.store'), $this->submission(['category' => 'search_engines']))
            ->assertRedirect(route('submit'))
            ->assertSessionHas('success');

        $submission = Submission::sole();
        $this->assertSame('Example Index', $submission->name);
        $this->assertSame(SubmissionStatus::Pending, $submission->status);
    }

    public function test_status_cannot_be_set_by_the_submitter(): void
    {
        $this->post(route('submit.store'), $this->submission(['status' => 'approved']));

        $this->assertSame(SubmissionStatus::Pending, Submission::sole()->status);
    }

    public function test_an_invalid_category_is_rejected(): void
    {
        $this->post(route('submit.store'), $this->submission(['category' => 'bogus']))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseCount('submissions', 0);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function nonHttpUrls(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'data' => ['data:text/html,<script>alert(1)</script>'],
            'file' => ['file:///etc/passwd'],
            'ftp' => ['ftp://example.com/'],
            'gopher' => ['gopher://127.0.0.1:6379/_INFO'],
        ];
    }

    #[DataProvider('nonHttpUrls')]
    public function test_only_http_and_https_urls_are_accepted(string $url): void
    {
        $this->post(route('submit.store'), $this->submission(['url' => $url]))
            ->assertSessionHasErrors('url');

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_honeypot_submissions_are_silently_dropped(): void
    {
        $this->post(route('submit.store'), $this->submission(['website' => 'https://spam.example']))
            ->assertRedirect(route('submit'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_the_form_renders_the_honeypot_field(): void
    {
        $this->get(route('submit'))->assertOk()->assertSee('name="website"', false);
    }

    public function test_submissions_are_rate_limited_per_minute(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('submit.store'), $this->submission())->assertRedirect();
        }

        $this->post(route('submit.store'), $this->submission())->assertTooManyRequests();

        $this->assertDatabaseCount('submissions', 5);
    }

    public function test_submissions_are_rate_limited_per_day(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->travel(2)->minutes();
            $this->post(route('submit.store'), $this->submission())->assertRedirect();
        }

        $this->travel(2)->minutes();
        $this->post(route('submit.store'), $this->submission())->assertTooManyRequests();

        $this->assertDatabaseCount('submissions', 20);
    }
}
