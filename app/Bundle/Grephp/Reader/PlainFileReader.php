<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\Reader;

use function is_resource;

/**
 * Reads normal (uncompressed) files.
 */
class PlainFileReader implements ReaderInterface
{
    private const EXCLUDED_EXTS = ['.gz', '.bz2', '.bzip2', '.bz', '.xz', '.zip'];

    public function iterate(string $path): iterable
    {
        if (!$this->supports($path)) {
            return; // empty iterator
        }
        $fp = @fopen($path, 'rb');
        if (is_resource($fp)) {
            yield ['logicalPath' => $path, 'stream' => $fp];
        }
    }

    public function supports(string $path): bool
    {
        $lower = strtolower($path);
        foreach (self::EXCLUDED_EXTS as $ext) {
            if (str_ends_with($lower, $ext)) {
                return false;
            }
        }

        return is_file($path);
    }
}
