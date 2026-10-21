<?php

namespace App\Console\Commands;

use App\Enums\IndexStatus;
use App\Models\Index;
use App\Support\HostResolver;
use App\Support\TruncatingStreamFilter;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CheckIndexLinks extends Command
{
    protected $signature = 'indexes:check-links {--id= : Check a single index by ID}';

    protected $description = 'Check index links for availability and parking pages';

    // Strings that appear in parking/expired domain pages
    private const PARKING_SIGNALS = [
        'domain for sale',
        'buy this domain',
        'this domain is for sale',
        'this domain has been registered',
        'domain is parked',
        'domain parking',
        'parked domain',
        'godaddy.com',
        'myparkingpage',
        'sedo.com',
        'hugedomains.com',
        'afternic.com',
        'namecheap.com/domains/registration',
        'dan.com/buy',
        'undeveloped.com',
    ];

    private const MAX_REDIRECTS = 5;

    // parking signals appear near the top of the page; don't download more than this
    private const MAX_BODY_BYTES = 512 * 1024;

    public function __construct(private readonly HostResolver $resolver)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        // entries an admin manually delisted keep their status
        $query = Index::query()->where('status_locked', false);

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }

        $indexes = $query->get();

        $bar = $this->output->createProgressBar($indexes->count());
        $bar->start();

        foreach ($indexes as $index) {
            $status = $this->check($index->url);

            $index->update([
                'status' => $status,
                'last_checked_at' => now(),
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return self::SUCCESS;
    }

    private function check(string $url): IndexStatus
    {
        try {
            [$response, $body] = $this->fetch($url);
        } catch (\Exception) {
            return IndexStatus::Dead;
        }

        $body = strtolower($body);

        if (! $response->successful()) {
            return IndexStatus::Inactive;
        }

        foreach (self::PARKING_SIGNALS as $signal) {
            if (str_contains($body, $signal)) {
                return IndexStatus::Inactive;
            }
        }

        return IndexStatus::Active;
    }

    /**
     * Follow redirects manually so every hop is checked against private addresses.
     *
     * @return array{Response, string}
     */
    private function fetch(string $url): array
    {
        for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
            [$response, $body] = $this->request($url);
            $location = $response->header('Location');

            if (! $response->redirect() || $location === '') {
                return [$response, $body];
            }

            $url = (string) UriResolver::resolve(new Uri($url), new Uri($location));
        }

        throw new RuntimeException('Too many redirects');
    }

    /**
     * @return array{Response, string}
     */
    private function request(string $url): array
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = trim((string) parse_url($url, PHP_URL_HOST), '[]');
        $port = parse_url($url, PHP_URL_PORT) ?? ($scheme === 'https' ? 443 : 80);

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            throw new RuntimeException("Refusing to fetch {$url}");
        }

        $address = $this->publicAddress($host);
        $sink = $this->sink();

        try {
            $response = Http::withUserAgent('Mozilla/5.0 (compatible; theindex.fyi/linkchecker)')
                ->timeout(15)
                ->withoutRedirecting()
                ->sink($sink)
                ->withOptions([
                    // connect to the address we vetted, so DNS can't change between check and request
                    'curl' => [CURLOPT_RESOLVE => ["{$host}:{$port}:" . (str_contains($address, ':') ? "[{$address}]" : $address)]],
                ])
                ->get($url);

            rewind($sink);

            return [$response, (string) stream_get_contents($sink)];
        } finally {
            fclose($sink);
        }
    }

    private function publicAddress(string $host): string
    {
        $addresses = $this->resolver->resolve($host);

        if ($addresses === []) {
            throw new RuntimeException("{$host} does not resolve");
        }

        foreach ($addresses as $address) {
            if (! $this->isPublic($address)) {
                throw new RuntimeException("{$host} resolves to non-public address {$address}");
            }
        }

        return $addresses[0];
    }

    private function isPublic(string $address): bool
    {
        // rejects loopback, private, link-local (cloud metadata), CGNAT and reserved ranges
        return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE) === $address;
    }

    /**
     * A temp stream that keeps only the first MAX_BODY_BYTES written to it.
     *
     * @return resource
     */
    private function sink()
    {
        if (! in_array(TruncatingStreamFilter::NAME, stream_get_filters(), true)) {
            stream_filter_register(TruncatingStreamFilter::NAME, TruncatingStreamFilter::class);
        }

        $sink = fopen('php://temp', 'w+b');

        if ($sink === false) {
            throw new RuntimeException('Could not open a temp stream');
        }

        stream_filter_append($sink, TruncatingStreamFilter::NAME, STREAM_FILTER_WRITE, self::MAX_BODY_BYTES);

        return $sink;
    }
}
