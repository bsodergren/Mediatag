<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp;

use Mediatag\Bundle\Grephp\FileSystem\FileEnumeratorInterface;
use Mediatag\Bundle\Grephp\FileSystem\RecursiveDirectoryEnumerator;
use Mediatag\Bundle\Grephp\Matcher\PatternMatcherInterface;
use Mediatag\Bundle\Grephp\Matcher\PregPatternMatcher;
use Mediatag\Bundle\Grephp\Reader\Bzip2Reader;
use Mediatag\Bundle\Grephp\Reader\GzipReader;
use Mediatag\Bundle\Grephp\Reader\PlainFileReader;
use Mediatag\Bundle\Grephp\Reader\ReaderInterface;
use Mediatag\Bundle\Grephp\Reader\Registry\DefaultReaderRegistry;
use Mediatag\Bundle\Grephp\Reader\Registry\ReaderRegistryInterface;
use Mediatag\Bundle\Grephp\Reader\XzReader;
use Mediatag\Bundle\Grephp\Reader\ZipReader;

use function is_resource;

/**
 * GrePHP — simple recursive grep utility for PHP codebases.
 *
 * Features:
 * - Recursively traverses directories using RecursiveDirectoryIterator
 * - Searches file contents with a PCRE pattern
 * - Supports reading compressed files: .gz, .bz2, .zip, and .xz (best-effort)
 * - Returns a map of file identifiers to lists of matched strings
 */
class Grephp
{
    private PatternMatcherInterface $matcher;

    private ReaderRegistryInterface $registry;

    private FileEnumeratorInterface $enumerator;

    private GrephpOptions $options;

    private StreamingGrep $streamingGrep;

    public function __construct(
        ?PatternMatcherInterface $matcher = null,
        ?ReaderRegistryInterface $registry = null,
        ?FileEnumeratorInterface $enumerator = null,
        ?GrephpOptions $options = null
    ) {
        $this->matcher  = $matcher ?? new PregPatternMatcher;
        $this->registry = $registry ?? new DefaultReaderRegistry([
            // Order matters only if multiple readers support the same path.
            new ZipReader,
            new GzipReader,
            new Bzip2Reader,
            new XzReader,
            new PlainFileReader,
        ]);
        $this->enumerator    = $enumerator ?? new RecursiveDirectoryEnumerator;
        $this->options       = $options ?? new GrephpOptions;
        $this->streamingGrep = new StreamingGrep($this->matcher);
    }

    /**
     * Recursively search files under a path for a regex pattern.
     * Returns a GrepResult containing matches and any matcher errors encountered.
     *
     * @param  string  $path  Path to a file or directory.
     * @param  string  $pattern  PCRE pattern, e.g. '/foo/i'.
     */
    public function grep(string $path, string $pattern): GrepResult
    {
        $result = new GrepResult;

        // Early validation: if the pattern itself is invalid, report once and stop.
        // We probe the matcher with an empty string to trigger any PCRE compilation errors
        // without scanning any files.
        $probe = $this->matcher->matchAll('', $pattern);
        if ($probe->hasError()) {
            $result->errors[] = new GrepError(
                file: null, // global pattern error, not tied to a specific file
                errorCode: $probe->errorCode,
                errorMessage: $probe->errorMessage
            );

            return $result;
        }

        if (is_dir($path)) {
            foreach ($this->enumerator->iterate($path) as $filePath) {
                $this->collectMatchesForPath($filePath, $pattern, $result);
            }
        } elseif (is_file($path)) {
            $this->collectMatchesForPath($path, $pattern, $result);
        }

        return $result;
    }

    /**
     * Returns whether invoking the external `xz` command is possible.
     */
    public static function isXzSupported(): bool
    {
        // Quick check: is proc_open disabled?
        if (! function_exists('proc_open')) {
            return false;
        }
        $disabled = ini_get('disable_functions');
        if (is_string($disabled) && stripos($disabled, 'proc_open') !== false) {
            return false;
        }
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open('xz --version', $descriptors, $pipes);
        if (! is_resource($process)) {
            return false;
        }
        // Close stdin immediately
        fclose($pipes[0]);
        // Read a bit of output
        stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $status = proc_close($process);

        return $status === 0;
    }

    /**
     * Use the registry to read logical files from path and collect matches and errors.
     */
    private function collectMatchesForPath(string $filePath, string $pattern, GrepResult $result): void
    {
        $reader = $this->registry->readerFor($filePath);
        if (! $reader instanceof ReaderInterface) {
            return;
        }
        foreach ($reader->iterate($filePath) as $entry) {
            $logical = $entry['logicalPath'] ?? $filePath;
            $stream  = $entry['stream'] ?? null;
            if (! is_resource($stream)) {
                continue;
            }
            $mr = $this->streamingGrep->grepStream($stream, $pattern, $this->options);
            fclose($stream);
            if (! empty($mr->matches)) {
                $result->matches[$logical] = $mr->matches;
            }
            if ($mr->hasError()) {
                $result->errors[] = new GrepError($logical, $mr->errorCode, $mr->errorMessage);
            }
        }
    }
}
