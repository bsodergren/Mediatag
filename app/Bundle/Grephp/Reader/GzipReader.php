<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\Reader;

use function is_resource;

/**
 * Reads .gz compressed files using the zlib stream wrapper.
 */
final class GzipReader implements ReaderInterface
{
    public function iterate(string $path): iterable
    {
        if (! $this->supports($path)) {
            return; // empty iterator
        }
        // Basic validation: gzip files start with magic bytes 1F 8B
        $hfp = @fopen($path, 'rb');
        if (! is_resource($hfp)) {
            return; // empty iterator
        }
        $header = fread($hfp, 2);
        fclose($hfp);
        if ($header === false || strlen((string) $header) < 2 || $header !== "\x1F\x8B") {
            return; // empty iterator
        }
        $fp = @fopen('compress.zlib://' . $path, 'rb');
        if (! is_resource($fp)) {
            return; // empty iterator
        }
        yield ['logicalPath' => $path, 'stream' => $fp];
    }

    public function supports(string $path): bool
    {
        $lower = strtolower($path);

        return is_file($path) && str_ends_with($lower, '.gz');
    }

    // openStream removed in unified interface
}
