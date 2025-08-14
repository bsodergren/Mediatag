<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Display;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\TagBuilder\TagReader;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use UTM\Bundle\Monolog\UTMLog;

use function array_key_exists;
use function count;
use function is_string;
use function strlen;

use const PHP_EOL;
use const STR_PAD_LEFT;

class Display
{
    public $BarSection1;
    public $BarBottom;

    public $LineBreaks = true;

    public $BarSection2;

    public $fileCountSection;

    public $fileInfoSection;

    public $blockDisplay;

    public $MetaBlockSection;

    public $BlockInfo;
    // public $MetaBlockSection;

    public $VideoInfoSection;

    public $processOutput;

    public $padbuffer = 3;

    public $text_style = 'current';

    public $padbufferChar = ' ';

    /**
     * @var int
     */
    public $displayTimer = 100000;

    protected $formatter;

    public function __construct(OutputInterface $output)
    {
        // utminfo();

        $this->formatter = new FormatterHelper();

        // $outputStyle = new OutputFormatterStyle('red');
        // Mediatag::$output->getFormatter()->setStyle('indent', $outputStyle);

        // $currentTagStyle = new OutputFormatterStyle('magenta');
        // Mediatag::$output->getFormatter()->setStyle('current', $currentTagStyle);

        // $updateTagStyle = new OutputFormatterStyle('bright-green');
        // Mediatag::$output->getFormatter()->setStyle('update', $updateTagStyle);

        $this->BarSection1      = Mediatag::$output->section();
        $this->BarSection2      = Mediatag::$output->section();
        $this->fileCountSection = Mediatag::$output->section();
        $this->fileInfoSection  = Mediatag::$output->section();
        $this->MetaBlockSection = Mediatag::$output->section();
        $this->processOutput    = Mediatag::$output->section();
        $this->VideoInfoSection = Mediatag::$output->section();
        $this->BarBottom        = Mediatag::$output->section();
    }

    public function DisplayTable(array $filelist_array)
    {
        $count = count($filelist_array);
        if (0 == $count) {
            Mediatag::$output->writeln('<error>No files found </error>');

            return;
        }
        $this->displayHeader(Mediatag::$output, ['count' => $count]);
        $idx = 1;
        foreach ($filelist_array as $key => $value) {
            $display = $this->displayFileInfo($value, $count, $idx);
            if (true === $display) {
                if (true == $this->LineBreaks) {
                    if ($count != $idx) {
                        $line_array = [];
                        for ($n = 0; $n < 7; ++$n) {
                            $line_array[] = '';
                        }
                        $line = implode(PHP_EOL, $line_array);
                        Mediatag::$output->writeln($line);
                    }
                }
            }
            ++$idx;
        }
        // UTMlog::logger('end File display');
    }

    public function displayHeader(OutputInterface $output, array $options): void
    {
        // UTMlog::logger('start Display Header');

        $count = $options['count'];
        if ($count > 0) {
            $this->fileCountSection->writeln('<comment>Found</comment> <info>'.$count.'</info> <comment> files</comment>');
            $this->fileInfoSection->writeln('<info>   </info>');
            $this->processOutput->setMaxHeight(9);
        }

        // $this->processOutput->writeLn('<info>   </info>');
    }

    public function displayFileInfo($fileinfo, $count, $idx)
    {
        // utminfo(func_get_args());

        $method   = 'overwrite';
        $tagCount = 0;
        if (!array_key_exists('currentTags', $fileinfo)) {
            $fileinfo['metatags'] = (new TagReader())->loadVideo($fileinfo)->getMetaValues();
            // utmdd([__METHOD__,$fileinfo]);

            $tagCount = count($fileinfo['metatags']);
        } else {
            $tagCount = count($fileinfo['currentTags']) + count($fileinfo['updateTags']);
        }

        // utmdd($fileinfo,$tagCount);

        $this->MetaBlockSection->setMaxHeight($tagCount + 6);
        $this->VideoInfoSection->setMaxHeight(3);

        if (0 == $tagCount) {
            utmdd($tagCount);
            $this->fileCountSection->{$method}('bl');
            $this->fileInfoSection->{$method}('bllll');
            $this->MetaBlockSection->{$method}('clllllllllllll');

            return false;
        }
        // $tagCount += 6;

        $this->blockDisplay = $this->DisplayMetaBlock($fileinfo);
        $this->blockDisplay = array_filter($this->blockDisplay);
        ksort($this->blockDisplay);
        $in_directory = (new Filesystem())->makePathRelative($fileinfo['video_path'], __CURRENT_DIRECTORY__);
        $filename     = $this->formatter->truncate($fileinfo['video_name'], __CONSOLE_WIDTH__);
        $this->fileCountSection->{$method}('<comment>Video </comment> <info>'.$idx.'</info> of <info>'.$count.'</info> files '.Mediatag::$tmpText);
        Mediatag::$tmpText = null;
        $this->fileInfoSection->{$method}('<info>'.$in_directory.$filename.'</info>');
        $this->MetaBlockSection->{$method}($this->blockDisplay);
        usleep($this->displayTimer);

        return true;
    }

    /**
     * @return array
     */
    public function sortBlocks($block)
    {
        // utminfo(func_get_args());

        $returnArray = [];
        $array       = [];
        foreach ($block as $row) {
            $success = preg_match('/\[(.*)\]\<\/([a-z=]+)>(.*)/i', $row, $matches);

            if ($success) {
                $array[$matches[1]][$matches[2]] = $matches[3];
            }
        }
        $len = 0;
        foreach ($array as $tag => $row) {
            $tagLen = strlen($tag);
            if ($tagLen > $len) {
                $len = $tagLen;
            }
        }

        foreach ($array as $tag => $row) {
            foreach ($row as $style => $value) {
                $spaces        = ($len + $this->padbuffer) - (strlen($tag) + 2);
                $value         = $this->indent($value, $spaces);
                $returnArray[] = str_replace("\t", '', $this->formatTagLine($tag, $value, $style));
            }
        }

        return $returnArray;
    }

    public function formatTagLine($tag, $value, $style = 'comment')
    {
        // utminfo(func_get_args());

        if (null !== $value) {
            // $change_value = "\t<" . $this->text_style . '>' . $this->formatter->truncate($value, __CONSOLE_WIDTH__ - 35) . '</>';
            $change_value = "\t<".$this->text_style.'>'.$value.'</>';

            return $this->indent($this->formatter->formatSection($tag, $change_value, $style), $this->padbuffer);
        }

        return '';
    }

    public function indent($string, $spaces)
    {
        // utminfo(func_get_args());

        if (is_string($string)) {
            return str_pad($string, strlen($string) + $spaces, $this->padbufferChar, STR_PAD_LEFT);
        }

        return $string;
    }

    private function DisplayMetaBlock($fileinfo): array
    {
        // utminfo(func_get_args());

        foreach (__META_TAGS__ as $tag) {
            $MetatagBlock[] = $this->TagBlockDisplay($tag, $fileinfo);
        }
        if (array_key_exists('updateTags', $fileinfo)) {
            foreach (__META_TAGS__ as $tag) {
                $MetaUpdateBlock[] = $this->UpdateTagBlockDisplay($tag, $fileinfo);
            }
            $MetatagBlock = array_merge($MetaUpdateBlock, $MetatagBlock);
            $MetatagBlock = $this->sortBlocks($MetatagBlock);
        }

        return $MetatagBlock;
    }

    private function TagBlockDisplay($tag, $fileinfo): ?string
    {
        // utminfo(func_get_args());

        $tag              = strtolower($tag);
        $style            = 'update';
        $this->text_style = 'fg=white';

        $string        = '';
        $current[$tag] = '';
        if (array_key_exists('currentTags', $fileinfo)) {
            $current          = $fileinfo['currentTags'];
            $this->text_style = 'current';
            $style            = 'current';
        }
        if (array_key_exists('metatags', $fileinfo)) {
            $current          = $fileinfo['metatags'];
            $this->text_style = 'fg=white';
            $style            = 'update';
        }
        if (array_key_exists('updateTags', $fileinfo)) {
            if (!array_key_exists($tag, $fileinfo['updateTags'])) {
                return $string;
            }
        }

        if (array_key_exists($tag, $current)) {
            $current_value = $current[$tag];
            if ('' != $current_value) {
                $string .= $this->formatTagLine($tag, $current_value, $style);
            }
        }

        return $string;
    }

    private function UpdateTagBlockDisplay($tag, $fileinfo): ?string
    {
        // utminfo(func_get_args());

        $tag     = strtolower($tag);
        $string  = '';
        $changes = [];
        $changes = $fileinfo['updateTags'];
        if (array_key_exists($tag, $changes)) {
            $change_value     = $changes[$tag];
            $this->text_style = 'update';

            $string .= $this->formatTagLine($tag, $change_value, 'update');
        }

        return $string;
    }
}
