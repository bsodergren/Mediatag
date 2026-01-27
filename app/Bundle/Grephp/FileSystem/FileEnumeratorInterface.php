<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\FileSystem;

/**
 * Enumerates file paths under a root directory.
 */
interface FileEnumeratorInterface
{
    /**
     * Iterate all file paths (regular files) under the given root directory, recursively.
     *
     * @return iterable<string> Yields absolute pathnames of files
     */
    public function iterate(string $root): iterable;
}
