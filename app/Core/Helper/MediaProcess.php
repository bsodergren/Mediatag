<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Symfony\Component\Console\Command\Command as SymCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use UTM\Bundle\Monolog\UTMLog;

trait MediaProcess
{
    public function exec($option = null)
    {
        // utminfo(func_get_args());

        $this->VideoList = parent::getVideoArray();
        if (0 == \count($this->VideoList['file'])) {
            return SymCommand::SUCCESS;
        }

        // UTMlog::logger('Videos found', \count($this->VideoList['file']));
    }

    public function setupDb()
    {
        $this->StorageConn = new Storage();

        return $this;
    }

    public function setupMap()
    {
        if (!\defined('ARTIST_MAP')) {
            $this->getArtistMap('ARTIST_MAP', $this->StorageConn->getArtistMap());
        }

        if (!\defined('IGNORE_NAME_MAP')) {
            $this->getArtistMap('IGNORE_NAME_MAP', $this->StorageConn->getIgnoredArists());
            // utmdd(IGNORE_NAME_MAP);
        }

        return $this;
    }

    public function setupFormat()
    {
        $this->formatter = new FormatterHelper();
        Mediatag::$output->getFormatter()->setStyle('id', new OutputFormatterStyle('yellow'));
        Mediatag::$output->getFormatter()->setStyle('text', new OutputFormatterStyle('green'));
        Mediatag::$output->getFormatter()->setStyle('error', new OutputFormatterStyle('red'));
        Mediatag::$output->getFormatter()->setStyle('playlist', new OutputFormatterStyle('bright-magenta'));
        Mediatag::$output->getFormatter()->setStyle('download', new OutputFormatterStyle('bright-blue'));
        Mediatag::$output->getFormatter()->setStyle('file', new OutputFormatterStyle('bright-cyan'));
    }

    public function __call($method, $args)
    {
        if (\array_key_exists($method, $this->commandList)) {
            foreach ($this->commandList[$method] as $cmd => $option) {
                if (method_exists($this, $cmd)) {
                    $this->{$cmd}($option);
                } else {
                    Mediatag::$output->writeln('<info>'.$cmd.' doesnt exist</info>');

                    return 0;
                }
            }
        } else {
            $this->process();
        }
    }
}
