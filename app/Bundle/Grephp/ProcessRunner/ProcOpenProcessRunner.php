<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\ProcessRunner;

use function is_resource;

/**
 * Default ProcessRunner using proc_open. When proc_open is unavailable or
 * process creation fails, a non-zero exit code is returned with empty output.
 */
final class ProcOpenProcessRunner implements StreamingProcessRunnerInterface
{
    public function run(string $command): ProcessResult
    {
        if (!function_exists('proc_open')) {
            return new ProcessResult(127, '', 'proc_open unavailable');
        }
        $desc = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $disabled = ini_get('disable_functions');
        if (is_string($disabled) && stripos($disabled, 'proc_open') !== false) {
            return new ProcessResult(127, '', 'proc_open disabled');
        }
        $proc = proc_open($command, $desc, $pipes);
        if (!is_resource($proc)) {
            return new ProcessResult(127, '', 'failed to start process');
        }
        // We don't need to provide stdin
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $code = proc_close($proc);
        return new ProcessResult($code, $stdout !== false ? $stdout : '', $stderr !== false ? $stderr : '');
    }

    public function runStreaming(string $command, callable $onStdoutChunk, ?callable $onStderrChunk = null): int
    {
        if (!function_exists('proc_open')) {
            return 127;
        }
        $disabled = ini_get('disable_functions');
        if (is_string($disabled) && stripos($disabled, 'proc_open') !== false) {
            return 127;
        }
        $desc = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = proc_open($command, $desc, $pipes);
        if (!is_resource($proc)) {
            return 127;
        }
        // Close stdin
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        // Read until the process exits
        $stdout = $pipes[1];
        $stderr = $pipes[2];
        while (true) {
            $r = [$stdout, $stderr];
            $w = null; $e = null;
            if (stream_select($r, $w, $e, 0, 200000) === false) {
                break;
            }
            foreach ($r as $stream) {
                $data = fread($stream, 8192);
                if ($data !== false && $data !== '') {
                    if ($stream === $stdout) {
                        $onStdoutChunk($data);
                    } elseif ($onStderrChunk) {
                        $onStderrChunk($data);
                    }
                }
            }
            $status = proc_get_status($proc);
            if (! $status || $status['running'] === false) {
                // Drain any remaining data
                $rest = stream_get_contents($stdout);
                if ($rest !== false && $rest !== '') { $onStdoutChunk($rest); }
                $rest = stream_get_contents($stderr);
                if ($onStderrChunk && $rest !== false && $rest !== '') { $onStderrChunk($rest); }
                break;
            }
        }
        fclose($stdout);
        fclose($stderr);

        return proc_close($proc);
    }
}
