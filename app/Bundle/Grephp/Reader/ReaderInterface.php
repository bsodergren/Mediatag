<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\Reader;

/**
 * Unified reader contract: streaming-first, container-agnostic.
 *
 * Readers yield one or more logical entries as readable PHP streams.
 * - For single-file formats (plain, .gz, .bz2, .xz) yield exactly one entry
 *   with logicalPath equal to the file path.
 * - For containers (.zip), yield one entry per regular file inside, using
 *   the logical path convention (e.g., zip://archive.zip#entry/name.txt).
 */
interface ReaderInterface
{
    /**
     * Whether this reader supports the given path (usually by extension or probing).
     */
    public function supports(string $path): bool;

    /**
     * Iterate logical entries as readable streams.
     * Each yielded item must be an array with keys:
     *  - logicalPath: string
     *  - stream: resource (readable)
     *
     * @return iterable<array{logicalPath:string, stream:resource}>
     */
    public function iterate(string $path): iterable;
}
