<?php

namespace Mediatag\Core\Traits\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Utilities\Option;

trait CommandInit
{
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // utminfo();
        $className = static::class;
        Option::init($input);

        if (Option::getValue('path', true) !== '') {
            $path = Option::getValue('path', true);
            if ($path !== null) {
                chdir($path);
            }
        }
        // utmdd(getcwd());

        $this->getLibrary($className::USE_LIBRARY);

        Option::set('USE_SEARCH', $className::USE_SEARCH);

        $this->loadDirs();
    }
}
