<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\Mediatag;
use UTM\Bundle\Monolog\UTMLog;
use Mediatag\Modules\Database\Storage;
use Mediatag\Traits\Callables;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymCommand;

class Process extends Mediatag
{
    use Callables;
    use Helper;

    /**
     * meta.
     */
    public $formatter;

    public $meta;

    public $displayTimer    = 0;

    public $ChangesArray    = [];

    public $VideoList;

    public $commandList     = [
        'empty'     => [
            'exec'      => null,
            'clearMeta' => null,
        ],
        'download'  => [
            'exec'         => null,
            'download'     => null,
            //     'writeChanges' => true,
        ],

        'list'      => [
            'exec'        => null,
            'getChanges'  => null,
            'saveChanges' => 'isset',
        ],
    ];

    public $defaultCommands = [
        'exec'         => null,
        'getChanges'   => null,
        // 'writeChanges' => null,
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
        utminfo();

        parent::boot($input, $output);
        $this->formatter   = new FormatterHelper();
        Mediatag::$output->getFormatter()->setStyle('id', new OutputFormatterStyle('yellow'));
        Mediatag::$output->getFormatter()->setStyle('text', new OutputFormatterStyle('green'));
        Mediatag::$output->getFormatter()->setStyle('error', new OutputFormatterStyle('red'));
        Mediatag::$output->getFormatter()->setStyle('playlist', new OutputFormatterStyle('bright-magenta'));
        Mediatag::$output->getFormatter()->setStyle('download', new OutputFormatterStyle('bright-blue'));
        Mediatag::$output->getFormatter()->setStyle('file', new OutputFormatterStyle('bright-cyan'));
        $this->StorageConn = new Storage();

        if (!\defined('ARTIST_MAP')) {
            $this->getArtistMap('ARTIST_MAP', $this->StorageConn->getArtistMap());
        }

        if (!\defined('IGNORE_NAME_MAP')) {
            $this->getArtistMap('IGNORE_NAME_MAP', $this->StorageConn->getIgnoredArists());
            // utmdd(IGNORE_NAME_MAP);

        }
        //        utmdd([__METHOD__,IGNORE_NAME_MAP]);
    }

    public function exec($option = null)
    {
        utminfo(func_get_args());



        $this->VideoList = parent::getVideoArray();

        if (count($this->VideoList['file']) == 0) {
            return SymCommand::SUCCESS;
        }

        // UTMlog::logger('Videos found', \count($this->VideoList['file']));
    }
}
