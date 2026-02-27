<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\Reader;

use function is_resource;

/**
 * Reads .bz2 (and related) compressed files using the bzip2 stream wrapper.
 */
final class Bzip2Reader implements ReaderInterface
{
    public function iterate(string $path): iterable
    {
        if (! $this->supports($path)) {
            return; // empty iterator
        }
        // Basic validation: bzip2 files start with magic bytes 'BZh'
        $hfp = @fopen($path, 'rb');
        if (! is_resource($hfp)) {
            return; // empty iterator
        }
        $header = fread($hfp, 3);
        fclose($hfp);
        if ($header === false || strlen((string) $header) < 3 || strncmp((string) $header, 'BZh', 3) !== 0) {
            return; // empty iterator
        }
        $fp = @fopen('compress.bzip2://' . $path, 'rb');
        if (! is_resource($fp)) {
            return; // empty iterator
        }
        yield ['logicalPath' => $path, 'stream' => $fp];
    }

    public function supports(string $path): bool
    {
        $lower = strtolower($path);

        return is_file($path)
            && (str_ends_with($lower, '.bz2') || str_ends_with($lower, '.bzip2') || str_ends_with($lower, '.bz'));
    }

    // openStream removed in unified interface
}
