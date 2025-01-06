<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use Mediatag\Traits\Translate;
use Mediatag\Modules\Database\DbMap;
use Mediatag\Core\Helper\MediaProcess;
use Nette\Utils\FileSystem as nFileSystem;
use Mediatag\Modules\VideoData\Data\VideoInfo;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem as SfSystem;

class Process extends Mediatag
{
    use Helper;
    use Lang;
    use Translate;
    use MediaProcess;

    public $defaultCommands = [
        'init' => null,
        'exec' => null,
    ];

    // public $commandList = [

    //     'create'    => ['createCommand' => null],
    //     'add'    => ['addCommand' => null],
    // ];


    public function __construct(InputInterface $input, OutputInterface $output, $file = null)
    {

        parent::boot($input, $output);

    }


}
