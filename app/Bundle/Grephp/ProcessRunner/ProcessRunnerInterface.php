<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\ProcessRunner;

interface ProcessRunnerInterface
{
    /**
     * Execute a shell command and return its result.
     */
    public function run(string $command): ProcessResult;
}
