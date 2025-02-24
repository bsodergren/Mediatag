<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Mediatag
{
    use Helper;
    use MediaExecute;
    use MediaProcess;

    /**
     * meta.
     */
    public $formatter;

    public $displayTimer = 0;

    public $ChangesArray = [];

    public $VideoList;

    public $commandList = [
        // 'empty'     => [
        //     'exec'      => null,
        //     'clearMeta' => null,
        // ],
        // 'download'  => [
        //     'exec'         => null,
        //     'download'     => null,
            //     'writeChanges' => true,
        // ],
        // 'clear'     => [
        //     'exec'      => null,
        //     'clearMeta' => null, ],
        // 'list'      => [
        //     'exec'        => null,
        //     'getChanges'  => null,
        //     //  'saveChanges' => 'isset',
        // ],
    ];

    public $defaultCommands = [
        // 'exec'         => null,
        // 'getChanges'   => null,
        // 'writeChanges' => null,
    ];

    protected $useFuncs = ['addMeta', 'setupMap'];

    protected $json_file;

    /**
     * __construct.
     *
     * @param mixed $input
     * @param mixed $output
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        // utminfo();

        parent::boot($input, $output);
        // $this->addMeta();

        // $this->setupFormat();
        //  $this->setupDb();
        // $this->setupMap();

        //        utmdd([__METHOD__,IGNORE_NAME_MAP]);
    }
}
