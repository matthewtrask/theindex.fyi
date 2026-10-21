<?php

use App\Enums\Category;
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

it('reaches every entry exactly once by following next links', function () {
    Index::factory()->count(7)->create();

    $slugs = [];
    $url = '/api/indexes?' . http_build_query(['page' => ['size' => 3]]);

    // bounded so a link that never advances fails instead of looping forever
    for ($requests = 0; $url !== null && $requests < 5; $requests++) {
        $response = $this->getJson($url)->assertOk();
        $slugs = [...$slugs, ...collect($response->json('data'))->pluck('attributes.slug')->all()];
        $url = $response->json('links.next');
    }

    expect($url)->toBeNull()
        ->and($slugs)->toHaveCount(7)
        ->and(array_unique($slugs))->toHaveCount(7);
});

it('keeps filters and page size in pagination links', function () {
    Index::factory()->count(5)->create(['category' => 'search_engines']);
    Index::factory()->count(5)->create(['category' => 'random_discovery']);

    $response = $this->getJson('/api/indexes?' . http_build_query([
        'filter' => ['category' => 'search_engines'],
        'page' => ['size' => 2],
    ]))->assertOk();

    $next = $response->json('links.next');
    parse_str((string) parse_url($next, PHP_URL_QUERY), $query);

    expect($query)->toMatchArray([
        'filter' => ['category' => 'search_engines'],
        'page' => ['number' => '2', 'size' => '2'],
    ]);

    $this->getJson($next)
        ->assertOk()
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.total', 5);
});

it('falls back to the default page size for out-of-range sizes', function (string $size) {
    Index::factory()->count(30)->create();

    $this->getJson('/api/indexes?' . http_build_query(['page' => ['size' => $size]]))
        ->assertOk()
        ->assertJsonPath('meta.per_page', 25)
        ->assertJsonCount(25, 'data');
})->with(['zero' => '0', 'negative' => '-1', 'not a number' => 'abc']);

it('ignores array-valued filters instead of erroring', function () {
    Index::factory()->count(2)->create();

    $this->getJson('/api/indexes?' . http_build_query(['filter' => ['category' => ['a', 'b']]]))
        ->assertOk()
        ->assertJsonPath('meta.total', 2);
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
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertExactJson(['errors' => [[
            'status' => '404',
            'title' => 'Not Found',
            'detail' => 'The requested resource does not exist.',
        ]]]);

    expect($response->getContent())->not->toContain('App\\Models');
});

it('returns JSON:API errors even without an Accept header', function () {
    $this->get('/api/indexes/does-not-exist')
        ->assertNotFound()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonPath('errors.0.status', '404');
});

it('returns a JSON:API error when rate limited', function () {
    for ($i = 0; $i < 60; $i++) {
        $this->getJson('/api/indexes')->assertOk();
    }

    $this->getJson('/api/indexes')
        ->assertTooManyRequests()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertHeader('Retry-After')
        ->assertJsonPath('errors.0.status', '429')
        ->assertJsonPath('errors.0.title', 'Too Many Requests');
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

// --- Attribute correctness ---

it('returns id as a string per JSON:API spec', function () {
    Index::factory()->create(['slug' => 'string-id-test']);

    $response = $this->getJson('/api/indexes/string-id-test');

    expect($response->json('data.id'))->toBeString();
});

it('returns correct attribute values for a known index', function () {
    $index = Index::factory()->create([
        'name' => 'My Index',
        'slug' => 'my-index',
        'url' => 'https://myindex.example.com',
        'description' => 'A test index.',
        'category' => Category::CuratedDirectories,
        'language' => 'fr',
        'accepts_submissions' => false,
        'last_checked_at' => null,
    ]);

    $response = $this->getJson('/api/indexes/my-index');

    $response->assertOk()
        ->assertJsonPath('data.attributes.name', 'My Index')
        ->assertJsonPath('data.attributes.slug', 'my-index')
        ->assertJsonPath('data.attributes.url', 'https://myindex.example.com')
        ->assertJsonPath('data.attributes.description', 'A test index.')
        ->assertJsonPath('data.attributes.category', 'curated_directories')
        ->assertJsonPath('data.attributes.language', 'fr')
        ->assertJsonPath('data.attributes.accepts_submissions', false)
        ->assertJsonPath('data.attributes.last_checked_at', null);
});

it('returns null for language when not set', function () {
    Index::factory()->create(['slug' => 'no-language', 'language' => null]);

    $response = $this->getJson('/api/indexes/no-language');

    $response->assertOk()
        ->assertJsonPath('data.attributes.language', null);
});

it('returns accepts_submissions as a boolean', function () {
    Index::factory()->create(['slug' => 'no-submissions', 'accepts_submissions' => false]);

    $response = $this->getJson('/api/indexes/no-submissions');

    expect($response->json('data.attributes.accepts_submissions'))->toBeBool();
});

// --- Combined filters ---

it('filters by both category and language', function () {
    Index::factory()->create(['category' => Category::CuratedDirectories, 'language' => 'es']);
    Index::factory()->create(['category' => Category::CuratedDirectories, 'language' => 'fr']);
    Index::factory()->create(['category' => Category::SearchEngines, 'language' => 'es']);

    $response = $this->getJson('/api/indexes?' . http_build_query([
        'filter' => ['category' => 'curated_directories', 'language' => 'es'],
    ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.total', 1);
});

// --- Empty state ---

it('returns an empty collection when no active indexes exist', function () {
    Index::factory()->inactive()->count(3)->create();

    $response = $this->getJson('/api/indexes');

    $response->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.total', 0);
});

// --- OpenAPI spec ---

it('serves the openapi spec at /api/openapi.yaml', function () {
    $response = $this->get('/api/openapi.yaml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/yaml');
});
