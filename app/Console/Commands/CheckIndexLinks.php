<?php

namespace App\Console\Commands;

use App\Enums\IndexStatus;
use App\Models\Index;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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

    public function handle(): int
    {
        $query = Index::query();

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
            $response = Http::withUserAgent('Mozilla/5.0 (compatible; theindex.fyi/linkchecker)')
                ->timeout(15)
                ->get($url);
        } catch (\Exception) {
            return IndexStatus::Dead;
        }

        if (! $response->successful()) {
            return IndexStatus::Inactive;
        }

        $body = strtolower($response->body());

        foreach (self::PARKING_SIGNALS as $signal) {
            if (str_contains($body, $signal)) {
                return IndexStatus::Inactive;
            }
        }

        return IndexStatus::Active;
    }
}
