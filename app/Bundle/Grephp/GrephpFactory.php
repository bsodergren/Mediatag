<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp;

use Mediatag\Bundle\Grephp\FileSystem\RecursiveDirectoryEnumerator;
use Mediatag\Bundle\Grephp\Matcher\PregPatternMatcher;
use Mediatag\Bundle\Grephp\Reader\Bzip2Reader;
use Mediatag\Bundle\Grephp\Reader\GzipReader;
use Mediatag\Bundle\Grephp\Reader\PlainFileReader;
use Mediatag\Bundle\Grephp\Reader\Registry\DefaultReaderRegistry;
use Mediatag\Bundle\Grephp\Reader\XzReader;
use Mediatag\Bundle\Grephp\Reader\ZipReader;

/**
 * Convenience factory for composing a Grephp instance with default components.
 */
final class GrephpFactory
{
    /**
     * Build a Grephp instance wired with the default implementations:
     * - RecursiveDirectoryEnumerator
     * - DefaultReaderRegistry (Zip, Gzip, Bzip2, Xz, Plain readers — in this order)
     * - PregPatternMatcher
     */
    public static function default(): Grephp
    {
        $matcher = new PregPatternMatcher();
        $registry = new DefaultReaderRegistry([
            new ZipReader(),
            new GzipReader(),
            new Bzip2Reader(),
            new XzReader(),
            new PlainFileReader(),
        ]);
        $enumerator = new RecursiveDirectoryEnumerator();
        $options = new GrephpOptions(); // defaults: auto-streaming with line mode

        return new Grephp($matcher, $registry, $enumerator, $options);
    }

    /**
     * Build a Grephp instance configured for streaming, with convenience parameters.
     */
    public static function streaming(
        ?int $streamThresholdBytes = 1_000_000,
        string $streamMode = GrephpOptions::STREAM_MODE_LINE,
        int $chunkSize = 64 * 1024,
        int $chunkOverlap = 1024,
        ?int $maxMatchPerFile = null
    ): Grephp {
        $matcher = new PregPatternMatcher();
        $registry = new DefaultReaderRegistry([
            new ZipReader(),
            new GzipReader(),
            new Bzip2Reader(),
            new XzReader(),
            new PlainFileReader(),
        ]);
        $enumerator = new RecursiveDirectoryEnumerator();
        $options = new GrephpOptions($streamThresholdBytes, $streamMode, $chunkSize, $chunkOverlap, $maxMatchPerFile);
        return new Grephp($matcher, $registry, $enumerator, $options);
    }
}
