<?php

namespace Mediatag\Core;

use Mediatag\Bundle\BashCompletion\Completion;
use Mediatag\Bundle\BashCompletion\Completion\ShellPathCompletion;
use Mediatag\Bundle\BashCompletion\CompletionCommand;
use Mediatag\Bundle\BashCompletion\CompletionHandler;

class MediaCompletionCommand extends CompletionCommand
{
    public $completionHandlers = [];

    protected function configureCompletion(CompletionHandler $handler)
    {
        MediaCommand::getCompletionHandler();

        // $this->getCompletionHandler();
        // $this->completion;
        // $handler->addHandlers(
        //     $this->completionHandlers
        // );

        // utmdump($handler->getInput());

        // try {
        //     return $this->handler->runCompletion();
        // } catch (\Exception $e) {
        //     // Suppress exceptions so that they are not displayed during
        //     // completion.
        // }
    }
}
