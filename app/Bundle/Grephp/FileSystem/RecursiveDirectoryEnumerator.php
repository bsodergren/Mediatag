<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\FileSystem;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Default enumerator that recursively yields all regular file paths under a root directory.
 */
final class RecursiveDirectoryEnumerator implements FileEnumeratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function iterate(string $root): iterable
    {
        if (! is_dir($root)) {
            if (is_file($root)) {
                // In case a file path is accidentally passed, be forgiving and yield it.
                yield $root;
            }

            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                yield $file->getPathname();
            }
        }
    }
}
