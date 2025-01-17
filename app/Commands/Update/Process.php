<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Symfony\Component\Console\Command\Command as SymCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Bundle\Monolog\UTMLog;

class Process extends Mediatag
{
    use Helper;
    use MediaProcess;

    /**
     * meta.
     */
    public $formatter;

    public $StorageConn;

    public $displayTimer = 0;

    public $ChangesArray = [];

    public $VideoList;

    public $commandList = [
        // 'empty'     => [
        //     'exec'      => null,
        //     'clearMeta' => null,
        // ],
        'download'  => [
            'exec'         => null,
            'download'     => null,
            //     'writeChanges' => true,
        ],
        'clear'    => [
            'exec'      => null,
            'clearMeta' => null,],
        'list'      => [
            'exec'        => null,
            'getChanges'  => null,
            //  'saveChanges' => 'isset',
        ],
    ];

    public $defaultCommands = [
        'exec'         => null,
        'getChanges'   => null,
       'writeChanges' => null,
    ];

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
      
        $this->setupFormat();
        $this->setupDb();
        $this->setupMap();

        //        utmdd([__METHOD__,IGNORE_NAME_MAP]);
    }

   
}
