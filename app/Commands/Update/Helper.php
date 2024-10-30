<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Nette\Utils\Callback;
use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use Mediatag\Traits\CaseHelper;
use Mediatag\Utilities\ScriptWriter;
use Symfony\Component\Process\Process;
use Mediatag\Modules\Executable\WriteExec;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Modules\TagBuilder\TagBuilder;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Console\Helper\ProgressBar;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;

trait Helper
{
    use CaseHelper;

    public $lineOut = false;
    /**
     * process.
     */
    public function setgenre($value)
    {
        utminfo(func_get_args());

        return $value;
    }

    public function settitle($value)
    {
        utminfo(func_get_args());

        return $value;
    }

    public function setstudio($value)
    {
        utminfo(func_get_args());

        return $value;
    }

    public function setartist($value)
    {
        utminfo(func_get_args());

        return $value;
    }

    public function setkeyword($value)
    {
        utminfo(func_get_args());

        return $value;
    }

    public function getArtistMap($constant, $file)
    {
        utminfo(func_get_args());

        $replacement = null;
        if (\is_string($file)) {
            if (is_file($file)) {
                $artistList = file_get_contents($file);

                $artistMap  = explode("\n", $artistList);
            }
        } else {
            $artistMap = $file;
        }

        foreach ($artistMap as $key => $nameArray) {

            if (\is_array($nameArray)) {
                $replacement = trim($nameArray[1]);
                $replacement = str_replace(' ', '_', $replacement);

                $name        = trim($nameArray[0]);
                $name        = str_replace(' ', '_', $name);
                $nameMap[]   = ['name' => strtolower($name), 'replacement' => $replacement];

            } else {

                $nameMap[] = strtolower(str_replace(' ', '_', $nameArray));

            }

        }
        \define($constant, $nameMap);
    }

    public function clearMeta($options = [])
    {
        utminfo(func_get_args());

        foreach ($this->VideoList['file'] as $key => $videoArray) {
            $Command          = new WriteExec($videoArray, Mediatag::$input, Mediatag::$output);
            $Command->Display = Mediatag::$Display;
            $Command->clearMeta($options);
        }
    }

    public function getChanges($options)
    {
        utminfo(func_get_args());
        if (null === $this->VideoList) {
            $this->exec();
        }

        $VideoList   = $this->VideoList['file'];
        $count        = \count($VideoList);
        $current_dir  = null;
        $prev_dir     = null;

        $nidx         = 0;
        $pidx         = 1;

        if (Option::isTrue('range')) {
            [$count, $nidx] = Mediatag::$finder->getRangeIds($count, 0);
        }

        ProgressBar::setFormatDefinition('custom', '<text>%index%</text> <file>%videoname%</file>');
        if (Option::isTrue('quiet') == true) {
            echo $count;
        }
        // $progressBar  = new ProgressBar(Mediatag::$Display->BarSection1, $count);
        // $progressBar->setBarWidth(__CONSOLE_WIDTH__ - 50);

        // $progressBar2 = new ProgressBar(Mediatag::$Display->BarSection2, $count);
        // $progressBar2->setFormat(' ');
        if (Option::isTrue('range')) {
            // $progressBar->start(null, $nidx - 1);
            // $progressBar2->start(null, $nidx - 1);
        }
        $idx                             = 1;
        Mediatag::$Display->displayHeader(Mediatag::$output, ['count' => $count]);
        Mediatag::$Display->displayTimer = $this->displayTimer;

        foreach ($VideoList as $key => $videoInfo) {

            $tagObj       = new tagReader();
            $tagObj->loadVideo($videoInfo);
            $tagBuilder = new tagBuilder($key, $tagObj);

            $videoArray  = $tagBuilder->getTags($videoInfo);

            if (\count($videoArray['updateTags']) > 0) {
                // $progressBar2->setFormat('custom');

                // $name                 = $videoInfo['video_path'] . '/' . $videoInfo['video_name'];
                // $message              = $name;
                // $videoArray = $videoInfo;
                $Command                      = new WriteExec($videoArray, Mediatag::$input, Mediatag::$output);
                $Command->Display             = Mediatag::$Display;
                Mediatag::$Display->BlockInfo = [];
                $videoBlockInfo               = null;
                Mediatag::$Display->displayFileInfo($videoArray, $count, $idx);
                if (! Option::isTrue('preview')) {
                    $Command->writeChanges();
                    $this->updateDbEntry($videoArray);
                }
    
                foreach (Mediatag::$Display->BlockInfo as $tag => $value) {
                    $value            = trim($value);
    
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
                    $line       = implode(\PHP_EOL, $line_array);
                    Mediatag::$output->write($line);
                }
                ++$idx;
                ++$nidx;
                //                Mediatag::$Console->writeln($nidx . " -> " . $message);
                // $progressBar2->setMessage($nidx, 'index');
                // $progressBar2->setMessage($message, 'videoname');
                unset($videoArray[$key]);
              
                $Command = null;
            }

            $tagObj = null;
            $tagBuilder = null;
            // $progressBar->advance();
            // $progressBar2->advance();
        }

        // $progressBar->finish();
        // $progressBar2->finish();
    }

    public function saveChanges($json_file = '')
    {
        utminfo(func_get_args());

        $this->json_file = $json_file;
        if (null !== $json_file) {
            if (file_exists($json_file)) {
                $json               = file_get_contents($json_file);
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
        utminfo(func_get_args());

        if (null !== $this->json_file) {
            $json_file = $this->json_file;
        } else {
            $json_file = getcwd() . '/tagList.json';
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
        utminfo(func_get_args());

        $videoList                       = $this->ChangesArray;
        $count                           = \count($videoList);
        $idx                             = 1;
        if (Option::isTrue('preview')) {
            $ScriptWriter = new ScriptWriter('changes.sh', __PLEX_HOME__ . '/Pornhub');
            // $ScriptWriter->addCmd('update', ['-f']);
            $ScriptWriter->updatePreview($videoList);
            $ScriptWriter->write();
        }
        $idx                             = 1;
        Mediatag::$Display->displayHeader(Mediatag::$output, ['count' => $count]);
        Mediatag::$Display->displayTimer = $this->displayTimer;

        foreach ($videoList as $key => $videoArray) {
            $tmpNetwork = '';
            $tmpStudio = '';
            // utmdump($videoArray);
            // if (array_key_exists("updateTags", $videoArray)) {
            //     $videoUpdates = $videoArray['updateTags'];
            //     if (array_key_exists("studio", $videoUpdates)) {
            //         $tmpStudio = $videoUpdates['studio'];
            //     }
            //     if (array_key_exists("network", $videoUpdates)) {

            //         $tmpNetwork = $videoUpdates['network'];
            //         if ($tmpNetwork !== null) {
            //             if($tmpStudio != $tmpNetwork){
            //                 $videoArray['updateTags']['studio'] = $tmpStudio . "/" . $tmpNetwork;
            //             }
            //         }
            //     } elseif (array_key_exists("network", $videoArray['currentTags'])) {
            //         $tmpNetwork = $videoArray['currentTags']['network'];
            //         if ($tmpNetwork !== null) {
            //             if($tmpStudio != $tmpNetwork){
            //                 $videoArray['updateTags']['studio'] = $tmpStudio . "/" . $tmpNetwork;
            //             }
            //         }

            //     }

            // }

            $Command                      = new WriteExec($videoArray, Mediatag::$input, Mediatag::$output);
            $Command->Display             = Mediatag::$Display;
            Mediatag::$Display->BlockInfo = [];
            $videoBlockInfo               = null;
            Mediatag::$Display->displayFileInfo($videoArray, $count, $idx);
            if (! Option::isTrue('preview')) {
                $Command->writeChanges();
                $this->updateDbEntry($videoArray);
            }

            foreach (Mediatag::$Display->BlockInfo as $tag => $value) {
                $value            = trim($value);

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
                $line       = implode(\PHP_EOL, $line_array);
                Mediatag::$output->write($line);
            }
            ++$idx;

            // $cursor->clearOutput();
        }
    }

    public function updateDbEntry($videoData)
    {
        utminfo(func_get_args());

        //  Mediatag::$dbconn->updateDBEntry($videoData['video_key'], $videoData);
    }

    public function download()
    {
        utminfo(func_get_args());

        Mediatag::$output->writeln("Checking files...");

        foreach ($this->VideoList['file'] as $videoInfo) {

            $video_filename = $videoInfo['video_name'];

            $match          = preg_match('/.*_?[0-9]{3,5}[pP]?\_[0-9\.]{2,6}[kK]?\_([0-9]{3,15})/', $video_filename, $output_array);
            if ($match == 1) {
                $number = $output_array[1];
                $file   = $this->getphdbUrl($number);
                $found  = $this->findUrl($number, $file);
                if ($found !== false) {
                    Mediatag::$output->write("<info>" . $video_filename . "</info>");

                    Mediatag::$output->write(" was found in <comment>" . basename($file) . "</comment>");
                    [$url,$id] = explode(";", $found);
                    $this->checkurl($url);
                    // Mediatag::$output->writeln("");

                }

            }

        }
    }

    public function checkurl($url)
    {
        utminfo(func_get_args());

        $client     = HttpClient::create();
        $response   = $client->request(
            'GET',
            $url,
        );

        $statusCode = $response->getStatusCode();
        if ($statusCode != '404') {
            Mediatag::$output->writeln(" and is " . $url);
        } else {
            Mediatag::$output->writeln(" but is 404");
        }
    }


    public function urlCallback($type, $buffer)
    {
        utminfo(func_get_args());

        if (Process::ERR === $type) {
            // echo 'ERR > '.$buffer;
        } else {
            $this->lineOut = trim($buffer);
        }
    }
    private function findUrl($number, $file)
    {
        utminfo(func_get_args());


        $this->lineOut = false;
        $callback      = Callback::check([$this, 'urlCallback']);

        $command       = [
            '/usr/bin/grep',
            $number,
            $file,
        ];
        $proccess      = new Process($command);
        // utmdd($proccess->getCommandLine());
        $proccess->run($callback);
        return $this->lineOut;
    }


}
