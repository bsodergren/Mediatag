<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Command\Command as SymCommand;

trait MediaProcess
{
    public function exec($option = null)
    {
        $this->VideoList = parent::getVideoArray();

        if (0 == \count($this->VideoList['file'])) {
            return SymCommand::SUCCESS;
        }
    }

    public function __call($method, $args)
    {
        if (\array_key_exists($method, $this->commandList)) {
            foreach ($this->commandList[$method] as $cmd => $option) {
                if (method_exists($this, $cmd)) {
                    $this->{$cmd}($option);
                } else {
                    Mediatag::$output->writeln('<info>'.__LINE__.':'.$cmd.' doesnt exist</info>');

                    return 0;
                }
            }
        } else {
            $this->process();
        }
    }
}
