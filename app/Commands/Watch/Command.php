<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Watch;

use Mediatag\Bundle\Daemon\EndlessCommand;

use Mediatag\Core\Mediatag;
use Psr\Log\LoggerAwareTrait;
use FFMpeg\Coordinate\TimeCode;
use Mediatag\Core\MediaCommand;
use Psr\Log\LoggerAwareInterface;
use Mediatag\Modules\Display\ShowDisplay;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\VideoData\Data\VideoPreview;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Watch Command';
const NAME        = 'watch';
#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand
{
    use Lang;
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = true;
   

//     protected function execute(InputInterface $input, OutputInterface $output): int
//     {
// //        Mediatag::$output->write('Updating timestamp... ');
       
//         $output->writeln('Updating timestamp...  ' . time());
//  sleep(2);

//     }
}
