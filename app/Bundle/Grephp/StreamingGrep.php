<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp;

use Mediatag\Bundle\Grephp\Matcher\MatchResult;
use Mediatag\Bundle\Grephp\Matcher\PatternMatcherInterface;

use function is_resource;

/**
 * StreamingGrep scans a PHP stream for pattern matches using a selected mode.
 */
final readonly class StreamingGrep
{
    public function __construct(private PatternMatcherInterface $matcher) {}

    /**
     * Read a stream and return matches according to the configured options.
     * The caller is responsible for closing the stream.
     *
     * @param  resource  $stream
     */
    public function grepStream($stream, string $pattern, GrephpOptions $options): MatchResult
    {
        if (! is_resource($stream)) {
            return new MatchResult([]);
        }

        return $options->streamMode === GrephpOptions::STREAM_MODE_CHUNK
            ? $this->grepChunked($stream, $pattern, $options)
            : $this->grepLineByLine($stream, $pattern, $options);
    }

    private function grepLineByLine($stream, string $pattern, GrephpOptions $options): MatchResult
    {
        $matches       = [];
        $batch         = [];
        $linesPerBatch = max(1, $options->linesPerBatch);
        $overlap       = max(0, $options->linesBatchOverlap);
        $errorCode     = null;
        $errorMessage  = null;

        $flushBatch = function () use (&$batch, $pattern, $options, &$matches, &$errorCode, &$errorMessage): bool {
            if (empty($batch)) {
                return false;
            }
            // Join with newlines to preserve typical text semantics
            $text  = implode("\n", $batch);
            $mr    = $this->matcher->matchAll($text, $pattern);
            $found = $mr->matches;
            if ($mr->hasError() && $errorCode === null) {
                $errorCode    = $mr->errorCode;
                $errorMessage = $mr->errorMessage;
            }
            if (! empty($found)) {
                foreach ($found as $m) {
                    $matches[] = $m;
                    if ($options->maxMatchPerFile !== null && count($matches) >= $options->maxMatchPerFile) {
                        return true; // signal to stop early
                    }
                }
            }

            return false;
        };

        while (! feof($stream)) {
            $line = fgets($stream);
            if ($line === false) {
                break;
            }
            $batch[] = rtrim($line, "\r\n");
            if (count($batch) >= $linesPerBatch) {
                if ($flushBatch()) {
                    return new MatchResult($matches, $errorCode, $errorMessage);
                }
                // Preserve overlap tail if configured
                if ($overlap > 0 && $overlap < count($batch)) {
                    $batch = array_slice($batch, -$overlap);
                } else {
                    $batch = [];
                }
            }
        }

        // Flush remaining lines
        if (! empty($batch)) {
            $flushBatch();
        }

        return new MatchResult($matches, $errorCode, $errorMessage);
    }

    private function grepChunked($stream, string $pattern, GrephpOptions $options): MatchResult
    {
        $matches      = [];
        $carry        = '';
        $chunkSize    = max(1024, $options->chunkSize);
        $overlap      = max(0, min($options->chunkOverlap, $chunkSize));
        $errorCode    = null;
        $errorMessage = null;

        while (! feof($stream)) {
            $chunk = fread($stream, $chunkSize);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $data  = $carry . $chunk;
            $mr    = $this->matcher->matchAll($data, $pattern);
            $found = $mr->matches;
            if ($mr->hasError() && $errorCode === null) {
                $errorCode    = $mr->errorCode;
                $errorMessage = $mr->errorMessage;
            }
            if (! empty($found)) {
                foreach ($found as $m) {
                    $matches[] = $m;
                    if ($options->maxMatchPerFile !== null && count($matches) >= $options->maxMatchPerFile) {
                        return new MatchResult($matches, $errorCode, $errorMessage);
                    }
                }
            }
            // Save last $overlap bytes for the next round
            if ($overlap > 0) {
                $carry = substr($data, -$overlap);
            } else {
                $carry = '';
            }
        }

        if ($carry !== '') {
            $mr = $this->matcher->matchAll($carry, $pattern);
            if ($mr->hasError() && $errorCode === null) {
                $errorCode    = $mr->errorCode;
                $errorMessage = $mr->errorMessage;
            }
            foreach ($mr->matches as $m) {
                $matches[] = $m;
                if ($options->maxMatchPerFile !== null && count($matches) >= $options->maxMatchPerFile) {
                    break;
                }
            }
        }

        return new MatchResult($matches, $errorCode, $errorMessage);
    }
}
