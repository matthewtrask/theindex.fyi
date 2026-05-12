<?php

use App\Enums\Category;
use App\Enums\IndexStatus;
use App\Models\Index;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const JSON_API_CONTENT_TYPE = 'application/vnd.api+json';

// --- GET /api/indexes ---

it('returns a list of active indexes', function () {
    Index::factory()->count(3)->create();
    Index::factory()->inactive()->create();
    Index::factory()->dead()->create();

    $response = $this->getJson('/api/indexes');

    $response->assertOk()
        ->assertHeader('Content-Type', JSON_API_CONTENT_TYPE)
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('meta.total', 3);
});

it('returns the correct JSON:API structure for a collection', function () {
    Index::factory()->create(['name' => 'Test Index', 'slug' => 'test-index']);

    $response = $this->getJson('/api/indexes');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'type',
                    'id',
                    'attributes' => [
                        'name', 'slug', 'url', 'description',
                        'category', 'language', 'accepts_submissions', 'last_checked_at',
                    ],
                    'links' => ['self'],
                ],
            ],
            'meta' => ['total', 'per_page', 'current_page', 'last_page'],
            'links' => ['self', 'first', 'last', 'prev', 'next'],
        ])
        ->assertJsonPath('data.0.type', 'indexes');
});

it('returns results ordered by name', function () {
    Index::factory()->create(['name' => 'Zebra Index']);
    Index::factory()->create(['name' => 'Alpha Index']);
    Index::factory()->create(['name' => 'Middle Index']);

    $response = $this->getJson('/api/indexes');

    $names = collect($response->json('data'))->pluck('attributes.name');
    expect($names->all())->toBe(['Alpha Index', 'Middle Index', 'Zebra Index']);
});

it('filters by category', function () {
    Index::factory()->create(['category' => Category::CuratedDirectories]);
    Index::factory()->create(['category' => Category::CuratedDirectories]);
    Index::factory()->create(['category' => Category::SearchEngines]);

    $response = $this->getJson('/api/indexes?' . http_build_query(['filter' => ['category' => 'curated_directories']]));

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 2);
});

it('filters by language', function () {
    Index::factory()->create(['language' => 'es']);
    Index::factory()->create(['language' => 'es']);
    Index::factory()->create(['language' => 'fr']);
    Index::factory()->create(['language' => null]);

    $response = $this->getJson('/api/indexes?' . http_build_query(['filter' => ['language' => 'es']]));

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 2);
});

it('paginates results', function () {
    Index::factory()->count(10)->create();

    $response = $this->getJson('/api/indexes?page[size]=3&page[number]=2');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.per_page', 3)
        ->assertJsonPath('meta.total', 10);
});

it('caps page size at 100', function () {
    Index::factory()->count(5)->create();

    $response = $this->getJson('/api/indexes?page[size]=999');

    $response->assertOk()
        ->assertJsonPath('meta.per_page', 100);
});

it('includes pagination links', function () {
    Index::factory()->count(5)->create();

    $response = $this->getJson('/api/indexes?page[size]=2&page[number]=1');

    $response->assertOk()
        ->assertJsonPath('links.prev', null)
        ->assertJsonPath('meta.last_page', 3);

    expect($response->json('links.next'))->not->toBeNull();
});

// --- GET /api/indexes/{slug} ---

it('returns a single active index by slug', function () {
    $index = Index::factory()->create(['slug' => 'my-index']);

    $response = $this->getJson('/api/indexes/my-index');

    $response->assertOk()
        ->assertHeader('Content-Type', JSON_API_CONTENT_TYPE)
        ->assertJsonStructure([
            'data' => [
                'type', 'id', 'attributes' => [
                    'name', 'slug', 'url', 'description',
                    'category', 'language', 'accepts_submissions', 'last_checked_at',
                ],
                'links' => ['self'],
            ],
        ])
        ->assertJsonPath('data.type', 'indexes')
        ->assertJsonPath('data.attributes.slug', 'my-index');
});

it('returns 404 for a non-existent slug', function () {
    $response = $this->getJson('/api/indexes/does-not-exist');

    $response->assertNotFound()
        ->assertJsonStructure(['message']);
});

it('returns 404 for an inactive index', function () {
    Index::factory()->inactive()->create(['slug' => 'inactive-index']);

    $response = $this->getJson('/api/indexes/inactive-index');

    $response->assertNotFound();
});

it('returns 404 for a dead index', function () {
    Index::factory()->dead()->create(['slug' => 'dead-index']);

    $response = $this->getJson('/api/indexes/dead-index');

    $response->assertNotFound();
});
