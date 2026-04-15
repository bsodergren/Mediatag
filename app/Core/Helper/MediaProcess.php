<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Command\Command as SymCommand;

use function array_key_exists;
use function count;

trait MediaProcess
{
    public function exec($option = null)
    {
        Mediatag::debug('Running Exec on MediaProcess Trait');

        $this->VideoList = parent::getVideoArray();
        if (count($this->VideoList['file']) == 0) {
            return SymCommand::SUCCESS;
        }
    }

    public function __call($method, $args)
    {
        if (array_key_exists($method, $this->commandList)) {
            Mediatag::debug('Running command from MediaProcess Trait ', [get_class($this), $method]);

            foreach ($this->commandList[$method] as $cmd => $option) {
                if ($cmd == 'handler') {
                    continue;
                }
                if (method_exists($this, $cmd)) {
                    $this->{$cmd}($option);
                } else {
                    Mediatag::$output->writeln('<info>' . __LINE__ . ':' . $cmd . ' doesnt exist</info>');

                    return 0;
                }
            }
        } else {
            Mediatag::debug('Running process from MediaProcess Trait ');

            $this->process();
        }
    }
}
