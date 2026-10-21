<?php

namespace Tests\Feature\Admin;

use App\Enums\IndexStatus;
use App\Models\Index;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class IndexUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function update(Index $index, array $overrides = []): TestResponse
    {
        return $this->patch(route('admin.indexes.update', $index), array_merge([
            'name' => $index->name,
            'url' => $index->url,
            'description' => $index->description,
            'category' => $index->category->value,
            'status' => $index->status->value,
        ], $overrides));
    }

    public function test_manually_delisting_an_entry_locks_its_status(): void
    {
        $index = Index::factory()->create();

        $this->update($index, ['status' => 'dead'])->assertRedirect(route('admin.indexes.index'));

        $this->assertSame(IndexStatus::Dead, $index->fresh()->status);
        $this->assertTrue($index->fresh()->status_locked);
    }

    public function test_reinstating_an_entry_unlocks_its_status(): void
    {
        $index = Index::factory()->dead()->create(['status_locked' => true]);

        $this->update($index, ['status' => 'active']);

        $this->assertFalse($index->fresh()->status_locked);
    }

    public function test_editing_other_fields_leaves_the_lock_alone(): void
    {
        $checkerMarkedDead = Index::factory()->dead()->create();

        $this->update($checkerMarkedDead, ['description' => 'Updated description']);

        $this->assertFalse($checkerMarkedDead->fresh()->status_locked);
    }

    public function test_invalid_category_and_status_are_rejected(): void
    {
        $index = Index::factory()->create();

        $this->update($index, ['category' => 'bogus', 'status' => 'bogus'])
            ->assertSessionHasErrors(['category', 'status']);
    }

    public function test_only_http_and_https_urls_are_accepted(): void
    {
        $index = Index::factory()->create();

        $this->update($index, ['url' => 'gopher://127.0.0.1:6379/_INFO'])
            ->assertSessionHasErrors('url');
    }
}
