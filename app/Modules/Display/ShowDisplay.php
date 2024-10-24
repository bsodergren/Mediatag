<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Display;

use Mediatag\Core\Mediatag;


use UTM\Bundle\Monolog\UTMLog;
use Mediatag\Modules\TagBuilder\TagReader;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ShowDisplay
{
    public $BarSection1;

    public $LineBreaks    = false;

    public $BarSection2;

    public $fileCountSection;

    public $fileInfoSection;

    public $blockDisplay;

    public $MetaBlockSection;

    public $tableSection;

    public $table;

    public $VideoInfoSection;

    public $processOutput;

    public $padbuffer     = 2;

    public $padbufferChar = ' ';

    /**
     * @var int
     */
    public $displayTimer  = 500000;

    protected $formatter;

    public function __construct(OutputInterface $output)
    {
        utminfo(func_get_args());

        $this->formatter        = new FormatterHelper();
        $outputStyle            = new OutputFormatterStyle('red');
        Mediatag::$output->getFormatter()->setStyle('indent', $outputStyle);
        $currentTagStyle        = new OutputFormatterStyle('gray');
        Mediatag::$output->getFormatter()->setStyle('current', $currentTagStyle);

        $this->BarSection1      = Mediatag::$output->section();
        $this->BarSection2      = Mediatag::$output->section();
        $this->fileCountSection = Mediatag::$output->section();
        $this->fileInfoSection  = Mediatag::$output->section();
        $this->tableSection     = Mediatag::$output->section();
        $this->table            = new Table($this->tableSection);
    }

    public function DisplayTable(array $filelist_array)
    {
        utminfo(func_get_args());

        // UTMlog::logger('start Display Table');
        $count = \count($filelist_array);
        if (0 == $count) {
            Mediatag::$output->writeln('<error>No files found </error>');

            return;
        }
        $this->displayHeader(Mediatag::$output, ['count' => $count]);
        $idx   = 1;
        // UTMlog::logger('start File display');
        // UTMlog::startLap();
        foreach ($filelist_array as $key => $value) {
            // UTMlog::watchlap('key', $key);
            $display = $this->displayFileInfo($value, $count, $idx);
            if (true === $display) {
                if (true == $this->LineBreaks) {
                    if ($count != $idx) {
                        $line_array = [];

                        for ($n = 0; $n < 7; ++$n) {
                            $line_array[] = '';
                        }
                        $line       = implode(\PHP_EOL, $line_array);
                        Mediatag::$output->write($line);
                    }
                }
            }
            ++$idx;
        }
        // UTMlog::logger('end File display');
    }

    public function displayHeader(OutputInterface $output, array $options): void
    {
        utminfo(func_get_args());

        // UTMlog::logger('start Display Header');

        $count = $options['count'];
        if ($count > 0) {
            $this->fileCountSection->writeLn('<comment>Found</comment> <info>' . $count . '</info> <comment> files</comment>');
            $this->fileInfoSection->writeLn('<info>   </info>');
            $this->table->setHeaders(['ID', 'Meta Value']);
            $this->table->render();
        }

        // $this->processOutput->writeLn('<info>   </info>');
    }

    public function displayFileInfo($fileinfo, $count, $idx)
    {
        utminfo(func_get_args());

        $method       = 'overwrite';

        if (!\array_key_exists('currentTags', $fileinfo)) {
            $fileinfo['metatags'] = (new TagReader())->loadVideo($fileinfo)->getMetaValues();
            $tagCount             = \count($fileinfo['metatags']);
        } else {
            $tagCount = \count($fileinfo['currentTags']) + \count($fileinfo['updateTags']);
        }
        if (0 == $tagCount) {
            return false;
        }

        foreach (__META_TAGS__ as $tag) {
            $MetatagBlock[$tag] = $this->TagBlockDisplay($tag, $fileinfo);
        }

        foreach ($MetatagBlock as $tag => $row) {
            $this->table->appendRow([$tag, $row]);
        }
        $in_directory = (new Filesystem())->makePathRelative($fileinfo['video_path'], __CURRENT_DIRECTORY__);
        $filename     = $this->formatter->truncate($fileinfo['video_name'], __CONSOLE_WIDTH__);

        $this->fileCountSection->{$method}('<comment>Video </comment> <info>' . $idx . '</info> of <info>' . $count . '</info> files');
        $this->fileInfoSection->{$method}('<info>' . $in_directory . $filename . '</info>');

        return true;
        // usleep($this->displayTimer);
    }

    private function TagBlockDisplay($tag, $fileinfo): string|null
    {
        utminfo(func_get_args());

        $tag           = strtolower($tag);
        $style         = 'comment';
        $string        = '';
        $current[$tag] = '';
        if (\array_key_exists('currentTags', $fileinfo)) {
            $current = $fileinfo['currentTags'];
            $style   = 'current';
        }
        if (\array_key_exists('metatags', $fileinfo)) {
            $current = $fileinfo['metatags'];
            $style   = 'info';
        }
        if (\array_key_exists('updateTags', $fileinfo)) {
            if (!\array_key_exists($tag, $fileinfo['updateTags'])) {
                return $string;
            }
        }
        if (\array_key_exists($tag, $current)) {
            $current_value = $current[$tag];
            if ('' != $current_value) {
                $string .= $current_value;
            }
        }

        return $string;
    }

    private function UpdateTagBlockDisplay($tag, $fileinfo): string|null
    {
        utminfo(func_get_args());

        $tag     = strtolower($tag);
        $string  = '';
        $changes = [];
        $changes = $fileinfo['updateTags'];
        if (\array_key_exists($tag, $changes)) {
            $change_value = $changes[$tag];
            $string .= $change_value;
        }

        return $string;
    }
}
