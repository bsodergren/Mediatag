<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\Reader;

use Mediatag\Bundle\Grephp\ProcessRunner\ProcessRunnerInterface;
use Mediatag\Bundle\Grephp\ProcessRunner\ProcOpenProcessRunner;
use Mediatag\Bundle\Grephp\ProcessRunner\StreamingProcessRunnerInterface;

use function is_resource;

/**
 * Reads .xz compressed files by invoking the external `xz -dc` command
 * through a provided ProcessRunner.
 */
final class XzReader implements ReaderInterface
{
    private ProcessRunnerInterface $runner;

    public function __construct(?ProcessRunnerInterface $runner = null)
    {
        $this->runner = $runner ?? new ProcOpenProcessRunner();
    }

    public function iterate(string $path): iterable
    {
        if (!$this->supports($path)) {
            return; // empty iterator
        }
        $cmd = 'xz -dc ' . escapeshellarg($path);
        // If the runner supports streaming, accumulate directly into a temp stream
        if ($this->runner instanceof StreamingProcessRunnerInterface) {
            $tmp = fopen('php://temp', 'w+b');
            if (!is_resource($tmp)) {
                return; // empty iterator
            }
            $code = $this->runner->runStreaming($cmd, function (string $chunk) use ($tmp): void {
                fwrite($tmp, $chunk);
            });
            if ($code !== 0) {
                fclose($tmp);
                return; // empty iterator
            }
            rewind($tmp);
            yield ['logicalPath' => $path, 'stream' => $tmp];
            return;
        }
        // Fallback to non-streaming execution then expose it as stream
        $result = $this->runner->run($cmd);
        if ($result->exitCode !== 0) {
            return; // empty iterator
        }
        $tmp = fopen('php://temp', 'w+b');
        if (!is_resource($tmp)) {
            return; // empty iterator
        }
        fwrite($tmp, $result->stdout);
        rewind($tmp);
        yield ['logicalPath' => $path, 'stream' => $tmp];
    }

    public function supports(string $path): bool
    {
        $lower = strtolower($path);

        return is_file($path) && str_ends_with($lower, '.xz');
    }

    // openStream removed in unified interface
}
