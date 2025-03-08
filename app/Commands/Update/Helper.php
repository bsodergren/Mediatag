<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\WriteMeta;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
// use Mediatag\Traits\CaseHelper;
use Mediatag\Modules\TagBuilder\TagBuilder;
use Mediatag\Modules\TagBuilder\TagReader;
use Nette\Utils\Callback;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Process;
use UTM\Utilities\Option;

trait Helper
{
    // use CaseHelper;

    public $lineOut = false;

    /**
     * process.
     */
    public function setgenre($value)
    {
        // utminfo(func_get_args());

        return $value;
    }

    public function settitle($value)
    {
        // utminfo(func_get_args());

        return $value;
    }

    public function setstudio($value)
    {
        // utminfo(func_get_args());

        return $value;
    }

    public function setartist($value)
    {
        // utminfo(func_get_args());

        return $value;
    }

    public function setkeyword($value)
    {
        // utminfo(func_get_args());

        return $value;
    }

    public function clearMeta($options = [])
    {

        // utminfo(func_get_args());
        $VideoList = $this->VideoList['file'];
        $count     = \count($VideoList);

        $progressBar = new ProgressBar(Mediatag::$Display->BarSection1, $count);
        $progressBar->setBarWidth(__CONSOLE_WIDTH__ - 50);

        foreach ($this->VideoList['file'] as $key => $videoArray) {
            $Command          = new WriteMeta($videoArray, Mediatag::$input, Mediatag::$output);
            $Command->Display = Mediatag::$Display;
            $Command->clearMeta($options);
            unset($Command);
            $progressBar->advance();
        }
    }

    public function getChanges($options)
    {
        if (null === $this->VideoList) {
            $this->exec();
        }

        $VideoList   = $this->VideoList['file'];
        $count       = \count($VideoList);
        $idx         = 1;
        $progressBar = new ProgressBar(Mediatag::$Display->BarSection1, $count);
        $progressBar->setBarWidth(__CONSOLE_WIDTH__ - 50);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:16s%/%estimated:-16s% %memory:6s%');
        ProgressBar::setFormatDefinition('custom', '<text>%index%</text> <file>%videoname%</file>');

        $progressBar2 = new ProgressBar(Mediatag::$Display->BarSection2, $count);
        $progressBar2->setFormat(' ');
        foreach ($VideoList as $key => $videoInfo) {

            $tagObj = new TagReader();
            $tagObj->loadVideo($videoInfo);
            $tagBuilder = new TagBuilder($key, $tagObj);

            $videoArray = $tagBuilder->getTags($videoInfo);
            $name       = str_replace(__CURRENT_DIRECTORY__, '.', $videoInfo['video_path']).'/'.$videoInfo['video_name'];
            $message    = $name;
            if (\count($videoArray['updateTags']) > 0) {
                $progressBar2->setFormat('custom');
                $this->ChangesArray[] = $videoArray;

                $progressBar2->setMessage($idx, 'index');
                $progressBar2->setMessage($message, 'videoname');
                ++$idx;
                $progressBar2->advance();
            }
            $progressBar->advance();
        }
        $progressBar2->finish();
    }

    // public function saveChanges($json_file = '')
    // {
    //     // utminfo(func_get_args());

    //     $this->json_file = $json_file;
    //     $i               = 0;
    //     if (null !== $json_file) {
    //         if (file_exists($json_file)) {
    //             $json       = file_get_contents($json_file);
    //             $videoArray = json_decode($json, 1);
    //             utmdd($videoArray);
    //             $this->displayTimer = 250000;
    //             $count              = \count($videoArray);
    //             foreach ($videoArray as $key => $videoInfo) {
    //                 $this->writeMetaToVideo($videoInfo, $count, $i++);
    //             }

    //             unlink($json_file);

    //             return true;
    //         }
    //     }
    //     $this->videoArraytoJson($videoArray);
    // }

    // public function videoArraytoJson($array)
    // {
    //     // utminfo(func_get_args());

    //     if (null !== $this->json_file) {
    //         $json_file = $this->json_file;
    //     } else {
    //         $json_file = getcwd().'/tagList.json';
    //     }
    //     if (\count($array) > 0) {
    //         if (file_exists($json_file)) {
    //             unlink($json_file);
    //         }
    //         $json = json_encode($array, \JSON_PRETTY_PRINT);
    //         Filesystem::writeFile($json_file, $json, false);
    //     }
    // }

    public function writeMetaToVideo($videoArray, $count = null, $index = null)
    {
        $Command                      = new WriteMeta($videoArray, Mediatag::$input, Mediatag::$output);
        $Command->Display             = Mediatag::$Display;
        Mediatag::$Display->BlockInfo = [];
        $videoBlockInfo               = null;
        if (null === $count) {
            $count = 1;
        }
        if (null === $index) {
            $index = 1;
        }

        $lines = Mediatag::$Display->displayFileInfo($videoArray, $count, $index);

        foreach (Mediatag::$Display->BlockInfo as $tag => $value) {
            $value = trim($value);

            $videoBlockInfo[] = Mediatag::$Display->formatTagLine($tag, $value, 'fg=blue');
        }
        // utmdump($videoBlockInfo);

        if (\is_array($videoBlockInfo)) {
            $videoBlockInfo = Mediatag::$Display->sortBlocks($videoBlockInfo);
            Mediatag::$Display->VideoInfoSection->overwrite($videoBlockInfo);
        }

        if (!Option::isTrue('preview')) {
            $Command->writeChanges();
            // $this->updateDbEntry($videoArray);
        }
    }

    public function writeChanges($options = '')
    {
        //     // utminfo(func_get_args());

        $videoList = $this->ChangesArray;
        $count     = \count($videoList);
        // utmdd([$videoList, $count]);
        $idx = 1;

        Mediatag::$Display->displayHeader(Mediatag::$output, ['count' => $count]);
        // Mediatag::$Display->displayTimer = $this->displayTimer;

        foreach ($videoList as $key => $videoArray) {
            $updateCount = \count($videoArray['updateTags']);
            $this->writeMetaToVideo($videoArray, $count, $idx);

            if ($count != $idx) {
                $line_array = [];
                for ($n = 0; $n < $updateCount + 5; ++$n) {
                    $line_array[] = ' ';
                    // Mediatag::$output->writeln($count.' '.$n);
                }
                $line = implode(\PHP_EOL, $line_array);
                Mediatag::$output->writeln($line);
            }

            ++$idx;

            // Mediatag::$Cursor->clearOutput();
        }
    }

    public function updateDbEntry($videoData)
    {
        // utminfo(func_get_args());

        Mediatag::$dbconn->updateDBEntry($videoData['video_key'], $videoData, false);
    }

    public function download()
    {
        // utminfo(func_get_args());

        Mediatag::$output->writeln('Checking files...');

        foreach ($this->VideoList['file'] as $videoInfo) {
            $video_filename = $videoInfo['video_name'];

            $match = preg_match('/.*_?[0-9]{3,5}[pP]?\_[0-9\.]{2,6}[kK]?\_([0-9]{3,15})/', $video_filename, $output_array);
            if (1 == $match) {
                $number = $output_array[1];
                $file   = $this->getphdbUrl($number);
                $found  = $this->findUrl($number, $file);
                if (false !== $found) {
                    Mediatag::$output->write('<info>'.$video_filename.'</info>');

                    Mediatag::$output->write(' was found in <comment>'.basename($file).'</comment>');
                    [$url,$id] = explode(';', $found);
                    $this->checkurl($url);
                    // Mediatag::$output->writeln("");
                }
            }
        }
    }

    public function checkurl($url)
    {
        // utminfo(func_get_args());

        $client   = HttpClient::create();
        $response = $client->request(
            'GET',
            $url,
        );

        $statusCode = $response->getStatusCode();
        if ('404' != $statusCode) {
            Mediatag::$output->writeln(' and is '.$url);
        } else {
            Mediatag::$output->writeln(' but is 404');
        }
    }

    public function urlCallback($type, $buffer)
    {
        // utminfo(func_get_args());

        if (Process::ERR === $type) {
            // echo 'ERR > '.$buffer;
        } else {
            $this->lineOut = trim($buffer);
        }
    }

    private function findUrl($number, $file)
    {
        // utminfo(func_get_args());

        $this->lineOut = false;
        $callback      = Callback::check([$this, 'urlCallback']);

        $command = [
            '/usr/bin/grep',
            $number,
            $file,
        ];
        $proccess = new Process($command);
        // utmdd($proccess->getCommandLine());
        $proccess->run($callback);

        return $this->lineOut;
    }
}
