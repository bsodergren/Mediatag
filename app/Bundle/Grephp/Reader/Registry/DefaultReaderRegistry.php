<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\Reader\Registry;

use Mediatag\Bundle\Grephp\Reader\ReaderInterface;

/**
 * Default implementation that picks the first reader whose `supports()` method returns true.
 */
final class DefaultReaderRegistry implements ReaderRegistryInterface
{
    /** @var ReaderInterface[] */
    private array $readers;

    /**
     * @param ReaderInterface[] $readers
     */
    public function __construct(array $readers)
    {
        $this->readers = $readers;
    }

    public function readerFor(string $path): ?ReaderInterface
    {
        foreach ($this->readers as $reader) {
            if ($reader->supports($path)) {
                return $reader;
            }
        }
        return null;
    }
}
