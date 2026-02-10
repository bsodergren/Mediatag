<?php

namespace Mediatag\Core\Traits\Command;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\SignalRegistry\SignalMap;

trait SignalEvents
{
    public static object $CommandEvent;

    // public static function

    public function cleanOnEvent()
    {
        $class = get_class($this);

        $this->output->writeln(PHP_EOL . sprintf('Command <info>%s</info> interupted with code <error>%s</error>',
            $class . ':' . $this->getName(),
            SignalMap::getSignalName($this->signal)
        )
        );
    }

    public function cleanOnTerminate()
    {
        $this->output->writeln(sprintf('<info>%s</info> Completed', $this->getName()));

        // utmdd(get_class_vars(Mediatag::class), get_class_vars(get_class($this)));
    }
}
