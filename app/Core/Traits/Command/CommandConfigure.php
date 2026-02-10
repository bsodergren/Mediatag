<?php

namespace Mediatag\Core\Traits\Command;

use Mediatag\Core\MediaOptions;
use Symfony\Component\Console\Completion\CompletionInput;

trait CommandConfigure
{
    public function configure(): void
    {
        $child                      = static::class;
        MediaOptions::$callingClass = $child;

        $this->setDefinition(MediaOptions::getDefinition($this->getName()));

        $arguments = MediaOptions::getArguments(
            $this->getName(),
            $this->getDescription(),
            function (CompletionInput $input) {
                return call_user_func([MediaOptions::$CmdClass, 'ArgumentClosure'], $input, $this->getName());
            }
        );
        if (is_array($arguments)) {
            $this->addArgument(...$arguments);
        }

        $arguments = MediaOptions::getArgument($this->getName());

        if (is_array($arguments)) {
            foreach ($arguments as $arg) {
                $this->addArgument(...$arg);
            }
        }
    }
}
