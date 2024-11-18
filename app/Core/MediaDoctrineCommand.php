<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use Mediatag\Core\Mediatag;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;

class MediaDoctrineCommand extends DoctrineCommand
{
    public function configure(): void
    {
        // utminfo(func_get_args());


        $this->setName(static::$defaultName)->setDescription(static::$defaultDescription);

        $definition = MediaOptions::get($this->getName());

        $this->setDefinition($definition);
    }
}
