<?php /** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\Reader;

use ZipArchive;

use function is_resource;

/**
 * Reads .zip archives and yields streams per entry.
 */
final class ZipReader implements ReaderInterface
{
    public function iterate(string $path): iterable
    {
        if (!$this->supports($path) || !class_exists(ZipArchive::class)) {
            return; // empty iterator
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return; // empty iterator
        }
        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (!$stat) {
                    continue;
                }
                $name = $stat['name'] ?? null;
                if ($name === null || str_ends_with((string)$name, '/')) {
                    continue; // skip directories
                }
                $logicalPath = 'zip://'.$path.'#'.$name;
                $fp = @fopen($logicalPath, 'rb');
                if (is_resource($fp)) {
                    yield ['logicalPath' => $logicalPath, 'stream' => $fp];
                }
            }
        } finally {
            $zip->close();
        }
    }

    public function supports(string $path): bool
    {
        $lower = strtolower($path);

        return is_file($path) && str_ends_with($lower, '.zip');
    }

    // iterateEntryStreams removed in unified interface
}
