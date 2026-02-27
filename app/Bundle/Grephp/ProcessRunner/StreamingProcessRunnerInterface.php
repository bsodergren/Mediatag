<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\ProcessRunner;

interface StreamingProcessRunnerInterface extends ProcessRunnerInterface
{
    /**
     * Execute a command and stream stdout/stderr to callbacks.
     * Returns the exit code.
     *
     * @param  callable(string):void  $onStdoutChunk
     * @param  callable(string):void|null  $onStderrChunk
     */
    public function runStreaming(string $command, callable $onStdoutChunk, ?callable $onStderrChunk = null): int;
}
