<?php

/**
 * JBZoo Toolbox - Cli.
 *
 * This file is part of the JBZoo Toolbox project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 *
 * @see        https://github.com/JBZoo/Cli
 */

declare(strict_types=1);

namespace JBZoo\Cli;

use Exception;
use JBZoo\Cli\CliCommand;
use JBZoo\Cli\OutputMods\AbstractOutputMode;
use JBZoo\Cli\OutputMods\Text;
use JBZoo\Event\EventManager;
use JBZoo\Utils\FS;
use Mediatag\Core\MediaApplication;
use Mediatag\Core\Traits\Command\CommandRun;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

use function JBZoo\Utils\isStrEmpty;

/**
 * @psalm-suppress ClassMustBeFinal
 */
class CliApplication extends MediaApplication
{
    use CommandRun;

    public $defaultCmd = 'list';

    private ?EventManager $eventManager = null;

    private ?string $logo = null;

    private ?AbstractOutputMode $outputMode = null;

    /**
     * Register commands by directory path.
     */
    public function registerCommandsByPath(
        string $commandsDir,
        string $globalNamespace,
        bool $strictMode = true
    ): self {
        if ($strictMode && ! \is_dir($commandsDir)) {
            throw new Exception('First argument is not directory!');
        }

        /** @var string[] $files */
        $files = FS::ls($commandsDir);
        $files = \array_filter($files, static fn (string $file) => \str_ends_with($file, '.php'));
        if (\count($files) === 0) {
            return $this;
        }

        foreach ($files as $file) {
            if (! \file_exists($file)) {
                continue;
            }
            require_once $file;

            $dir         = \dirname($file);
            $commandsPos = \strpos($dir, 'Controller');

            if ($commandsPos !== false) {
                $taskNamespace = \trim(\str_replace(
                    '/',
                    '\\',
                    \substr($dir, $commandsPos + \strlen('Controller'))
                ), '\\');
            } else {
                $taskNamespace = '';
            }

            $commandClassName = "{$globalNamespace}\\{$taskNamespace}\\" . FS::filename($file);
            if (\class_exists($commandClassName)) {
                $reflection = new \ReflectionClass($commandClassName);
            } else {
                throw new Exception("Command/Class \"{$commandClassName}\" can't be loaded from the file \"{$file}\"");
            }

            if (! $reflection->isAbstract() && $reflection->isSubclassOf(CliCommand::class)) {
                $command = $reflection->newInstance();
                if ($command::$DEFAULT_CMD === true) {
                    $this->defaultCmd = $command->getName();
                }
                $this->addCommand($command);
            }
        }

        return $this;
    }

    public function setEventManager(EventManager $eventManager): self
    {
        $this->eventManager = $eventManager;

        return $this;
    }

    public function getEventManager(): ?EventManager
    {
        return $this->eventManager;
    }

    public function setLogo(?string $logo = null): self
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @SuppressWarnings(ShortVariable)
     */
    public function renderThrowable(\Throwable $e, OutputInterface $output): void
    {
        if ($this->outputMode === null || $this->outputMode instanceof Text) {
            parent::renderThrowable($e, $output);
        }
    }

    /**
     * Returns the long version of the application.
     */
    public function getLongVersion(): string
    {
        if (! isStrEmpty($this->logo)) {
            return "<info>{$this->logo}</info>\n<comment>{$this->getVersion()}</comment>";
        }

        return parent::getLongVersion();
    }

    public function setOutputMode(AbstractOutputMode $outputMode): self
    {
        $this->outputMode = $outputMode;

        return $this;
    }
}
