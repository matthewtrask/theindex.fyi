<?php

namespace App\Support;

use php_user_filter;

/**
 * Stream write filter that keeps the first N bytes (passed as the filter
 * param) and silently discards the rest, so a huge response can't exhaust memory.
 */
class TruncatingStreamFilter extends php_user_filter
{
    public const NAME = 'theindex.truncate';

    private int $remaining = 0;

    public function onCreate(): bool
    {
        $this->remaining = (int) $this->params;

        return true;
    }

    public function filter($in, $out, &$consumed, bool $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $consumed += $bucket->datalen;

            if ($this->remaining > 0) {
                $bucket->data = substr($bucket->data, 0, $this->remaining);
                $this->remaining -= strlen($bucket->data);
                stream_bucket_append($out, $bucket);
            }
        }

        return PSFS_PASS_ON;
    }
}
