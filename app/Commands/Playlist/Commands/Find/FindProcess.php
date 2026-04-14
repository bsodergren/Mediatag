<?php

namespace Mediatag\Commands\Playlist\Commands\Find;

use Mediatag\Commands\Playlist\Commands\Find\FindHelper;
use Mediatag\Commands\Playlist\Process;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindProcess extends Process
{
    use FindHelper;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        MediaFinder::$quiet = true;
        parent::boot($input, $output);
        $this->VideoList = parent::getVideoArray();
    }
}
