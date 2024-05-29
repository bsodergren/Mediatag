<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\WriteExec;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\TagBuilder\TagBuilder;
use Mediatag\Modules\TagBuilder\TagReader;
use UTM\Utilities\Option;
use Mediatag\Utilities\ScriptWriter;
use Symfony\Component\Console\Helper\ProgressBar;

trait Helper
{
    /**
     * process.
     */
    public function setgenre($value)
    {
        return $value;
    }

    public function settitle($value)
    {
        return $value;
    }

    public function setstudio($value)
    {
        return $value;
    }

    public function setartist($value)
    {
        return $value;
    }

    public function setkeyword($value)
    {
        return $value;
    }

    public function getArtistMap($constant, $file)
    {
        $replacement = null;
        if (\is_string($file)) {
            if (is_file($file)) {
                $artistList = file_get_contents($file);

                $artistMap = explode("\n", $artistList);
            }
        } else {
            $artistMap = $file;
        }

        foreach ($artistMap as $key => $nameArray) {

            if (\is_array($nameArray)) {
                $replacement = trim($nameArray[1]);
                $replacement = str_replace(' ', '_', $replacement);

                $name = trim($nameArray[0]);
                $name = str_replace(' ', '_', $name);
                $nameMap[] = ['name' => strtolower($name), 'replacement' => $replacement];

            } else {

                $nameMap[] = strtolower(str_replace(' ', '_', $nameArray));

                //                $tmp[0]    = $nameArray;
                //               unset($namesArray);
                //              $nameArray = $tmp;
            }

            //    $nameMap[] = strtolower($name);
        }
        \define($constant, $nameMap);
    }

    public function clearMeta($options = [])
    {
        foreach ($this->VideoList['file'] as $key => $videoArray) {
            $Command = new WriteExec($videoArray, Mediatag::$input, Mediatag::$output);
            $Command->Display = Mediatag::$Display;
            $Command->clearMeta($options);
        }
    }

    public function getChanges($options)
    {
        if (null === $this->VideoList) {
            $this->exec();
        }

        $videoArray = $this->VideoList['file'];

        $count = \count($videoArray);
        $current_dir = null;
        $prev_dir = null;

        $nidx = 0;
        $pidx = 1;

        if (Option::isTrue('range')) {
            [$count, $nidx] = Mediatag::$finder->getRangeIds($count, 0);
        }

        ProgressBar::setFormatDefinition('custom', '<text>%index%</text> <file>%videoname%</file>');
        if (Option::isTrue('quiet') == true) {
            echo $count;
        }
        $progressBar = new ProgressBar(Mediatag::$Display->BarSection1, $count);
        $progressBar->setBarWidth(__CONSOLE_WIDTH__ - 50);

        $progressBar2 = new ProgressBar(Mediatag::$Display->BarSection2, $count);
        $progressBar2->setFormat(' ');
        if (Option::isTrue('range')) {
            $progressBar->start(null, $nidx - 1);
            $progressBar2->start(null, $nidx - 1);
        }

        $tagObj = new tagReader();

        foreach ($videoArray as $key => $videoInfo) {
            $tagObj->loadVideo($videoInfo);
            $tagBuilder = new tagBuilder($key, $tagObj);

            $videoInfo = $tagBuilder->getTags($videoInfo);

            $message = 'No Update';

            if (\count($videoInfo['updateTags']) > 0) {
                $progressBar2->setFormat('custom');

                $name = $videoInfo['video_path'].'/'.$videoInfo['video_name'];
                $message = $name;
                $this->ChangesArray[] = $videoInfo;
                ++$nidx;
                //                Mediatag::$Console->writeln($nidx . " -> " . $message);
                $progressBar2->setMessage($nidx, 'index');
                $progressBar2->setMessage($message, 'videoname');
            }

            $progressBar->advance();
            $progressBar2->advance();
        }

        $progressBar->finish();
        $progressBar2->finish();
    }

    public function saveChanges($json_file = '')
    {
        $this->json_file = $json_file;
        if (null !== $json_file) {
            if (file_exists($json_file)) {
                $json = file_get_contents($json_file);
                $this->ChangesArray = json_decode($json, 1);
                $this->displayTimer = 250000;
                $this->writeChanges();
                unlink($json_file);

                return true;
            }
        }
        $this->videoArraytoJson($this->ChangesArray);
    }

    public function videoArraytoJson($array)
    {
        if (null !== $this->json_file) {
            $json_file = $this->json_file;
        } else {
            $json_file = getcwd().'/tagList.json';
        }
        if (\count($array) > 0) {
            if (file_exists($json_file)) {
                unlink($json_file);
            }
            $json = json_encode($array, \JSON_PRETTY_PRINT);
            Filesystem::writeFile($json_file, $json, false);
        }
    }

    public function writeChanges($options = '')
    {
        $videoList = $this->ChangesArray;
        $count = \count($videoList);
        $idx = 1;
        if (Option::isTrue('preview')) {
            $ScriptWriter = new ScriptWriter('changes.sh', __PLEX_HOME__.'/Pornhub');
            // $ScriptWriter->addCmd('update', ['-f']);
            $ScriptWriter->updatePreview($videoList);
            $ScriptWriter->write();
        }
        Mediatag::$Display->displayHeader(Mediatag::$output, ['count' => $count]);
        Mediatag::$Display->displayTimer = $this->displayTimer;
        foreach ($videoList as $key => $videoArray) {
            $Command = new WriteExec($videoArray, Mediatag::$input, Mediatag::$output);
            $Command->Display = Mediatag::$Display;
            Mediatag::$Display->BlockInfo = [];
            $videoBlockInfo = null;
            Mediatag::$Display->displayFileInfo($videoArray, $count, $idx);
            if (! Option::isTrue('preview')) {
                $Command->writeChanges();
                $this->updateDbEntry($videoArray);
            }

            foreach (Mediatag::$Display->BlockInfo as $tag => $value) {
                $value = trim($value);

                $videoBlockInfo[] = Mediatag::$Display->formatTagLine($tag, $value, 'fg=blue');
            }
            if (\is_array($videoBlockInfo)) {
                $videoBlockInfo = Mediatag::$Display->sortBlocks($videoBlockInfo);
                Mediatag::$Display->VideoInfoSection->overwrite($videoBlockInfo);
            }

            if ($count != $idx) {
                $line_array = [];
                for ($n = 0; $n < 9; ++$n) {
                    $line_array[] = '';
                }
                $line = implode(\PHP_EOL, $line_array);
                Mediatag::$output->write($line);
            }
            ++$idx;

            // $cursor->clearOutput();
        }
    }

    public function updateDbEntry($videoData)
    {
        //  Mediatag::$dbconn->updateDBEntry($videoData['video_key'], $videoData);
    }
}
