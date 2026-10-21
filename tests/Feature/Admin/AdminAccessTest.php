<?php

namespace Tests\Feature\Admin;

use App\Enums\SubmissionStatus;
use App\Models\Index;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function adminPages(): array
    {
        return [
            'stats' => ['/admin/stats'],
            'submissions' => ['/admin/submissions'],
            'indexes' => ['/admin/indexes'],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_guests_are_redirected_to_login(string $uri): void
    {
        $this->get($uri)->assertRedirect(route('login'));
    }

    #[DataProvider('adminPages')]
    public function test_non_admin_users_are_forbidden(string $uri): void
    {
        $this->actingAs(User::factory()->create())
            ->get($uri)
            ->assertForbidden();
    }

    #[DataProvider('adminPages')]
    public function test_admin_users_can_view_admin_pages(string $uri): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get($uri)
            ->assertOk();
    }

    public function test_non_admin_users_cannot_view_the_index_edit_page(): void
    {
        $index = Index::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.indexes.edit', $index))
            ->assertForbidden();
    }

    public function test_non_admin_users_cannot_update_an_index(): void
    {
        $index = Index::factory()->create(['url' => 'https://original.example']);

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.indexes.update', $index), [
                'name' => $index->name,
                'url' => 'https://attacker.example',
                'description' => $index->description,
                'category' => $index->category->value,
                'status' => $index->status->value,
            ])
            ->assertForbidden();

        $this->assertSame('https://original.example', $index->fresh()->url);
    }

    public function test_non_admin_users_cannot_delete_an_index(): void
    {
        $index = Index::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.indexes.destroy', $index))
            ->assertForbidden();

        $this->assertModelExists($index);
    }

    public function test_non_admin_users_cannot_review_submissions(): void
    {
        $submission = Submission::create([
            'name' => 'Example',
            'url' => 'https://example.com',
            'description' => 'An example index',
            'status' => SubmissionStatus::Pending,
        ]);

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.submissions.update', $submission), ['status' => 'approved'])
            ->assertForbidden();

        $this->assertSame(SubmissionStatus::Pending, $submission->fresh()->status);
    }

    public function test_is_admin_cannot_be_mass_assigned(): void
    {
        $user = User::create([
            'name' => 'Mallory',
            'email' => 'mallory@example.com',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $this->assertFalse($user->fresh()->is_admin);
    }
}
