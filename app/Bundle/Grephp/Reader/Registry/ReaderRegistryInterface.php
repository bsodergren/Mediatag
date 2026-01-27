<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\Reader\Registry;

use Mediatag\Bundle\Grephp\Reader\ReaderInterface;

interface ReaderRegistryInterface
{
    /**
     * Returns a reader capable of handling the given path, or null if none.
     */
    public function readerFor(string $path): ?ReaderInterface;
}
