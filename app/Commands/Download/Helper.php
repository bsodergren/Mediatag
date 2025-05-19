<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Download;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Traits\MediaFFmpeg;
use Mediatag\Utilities\Chooser;
use Mediatag\Utilities\ScriptWriter;
use Mediatag\Utilities\Strings;
use Symfony\Component\Process\Process as ExecProcess;
use UTM\Utilities\Option;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;

trait Helper
{
    use MediaFFmpeg;

    public function convertVideos()
    {
        // utminfo(func_get_args());

        $file_array = [];
        $file_array = Mediatag::$finder->Search(__CURRENT_DIRECTORY__, '*.mkv', exit: false);

        if (null === $file_array) {
            return 0;
        }
        if (Option::isTrue('max')) {
            $total      = (int) Option::getValue('max');
            $file_array = \array_slice($file_array, 0, $total);
        }

        $count = \count($file_array);
        if ($count > 0) {
            $this->textSection = Mediatag::$output->section();
            $this->barSection  = Mediatag::$output->section();
            $this->textSection->writeln('<info> Found '.$count.' files to convert</info>');
            foreach ($file_array as $k => $file) {
                $this->textSection->write('<comment> Converting <info>'.basename($file, '.mkv').'</info>... </comment>');
                $this->convertVideo($file, str_ireplace('.mkv', '.mp4', $file));
                $this->textSection->overwrite('<comment> finished </comment>');
            }
        }
    }

    public function jSonCache()
    {
        // utminfo(func_get_args());

        $file_array = Mediatag::$finder->Search(__CURRENT_DIRECTORY__, '*.json', exit: false);

        foreach ($file_array as $key => $file) {
            $videoInfo               = File::file($file);
            $videoInfo['video_file'] = str_replace('.info.json', '.mp4', $videoInfo['video_file']);
            $videoInfo['video_name'] = str_replace('.info.json', '.mp4', $videoInfo['video_name']);
            if (!Mediatag::$filesystem->exists($videoInfo['video_file'])) {
                $this->moveJson($videoInfo);
            }
            // utmdd($videoInfo);
        }

        if (\count($this->filesToRemove) > 0) {
            $this->cleanDupeFiles();
        }
    }

    public function moveDownloads()
    {
        // utminfo(func_get_args());

        $file_array = Mediatag::$finder->Search(__CURRENT_DIRECTORY__, '*.mp4', exit: false);

        $this->moveJson();
        $this->moveCaption();

        if ($file_array !== null) {

            foreach ($file_array as $key => $file) {
                $videoInfo = File::file($file);
                $ytdl_file = $videoInfo['video_file'].'.ytdl';
                $temp_file = str_replace('mp4', 'temp.mp4', $videoInfo['video_file']);
                if (file_exists($ytdl_file)) {
                    Mediatag::$output->writeln($ytdl_file);

                    continue;
                }
                if (file_exists($temp_file)) {
                    Mediatag::$output->writeln($temp_file);

                    continue;
                }


                $out = $this->moveVideo($videoInfo);


                Mediatag::$output->writeln($out);
            }
        }
        if (\count($this->newFiles) > 0) {
            $ScriptWriter = new ScriptWriter('addedFiles.sh', __PLEX_HOME__.'/Pornhub');
            $ScriptWriter->addCmd('update', ['-f']);
            $ScriptWriter->addFileList($this->newFiles);
            $ScriptWriter->write();
        }



        if (\count($this->filesToRemove) > 0) {
            $this->cleanDupeFiles();
        }

        if (!Option::istrue('test')) {
            Filesystem::prunedirs();
        }

        Mediatag::$output->writeln('Done');
    }

    private function moveJson()
    {
        // utminfo(func_get_args());


        $fileArray = $this->searchDownloads("json");
        if ($fileArray !== null) {
            foreach ($fileArray as $row) {
                $key = $row['key'];
                $json_file = $row['src'];
                $newJson_file = __JSON_CACHE_DIR__.'/'.$key.'.info.json';


                if (!Mediatag::$filesystem->exists($newJson_file)) {
                    if (Option::istrue('test')) {
                        $out = "<question>jSon</question>\n\t<comment>Old:".basename($json_file)."</comment>\n\t<info>New:".basename($newJson_file).'</info>';
                        Mediatag::$output->writeln($out);
                    } else {
                        Mediatag::$filesystem->rename($json_file, $newJson_file, false);
                    }
                } else {
                    $this->filesToRemove[] = $json_file;
                    $out                   = '<question>'.basename($newJson_file).' already exists</question>';
                    Mediatag::$output->writeln($out);
                }

            }
        }

    }
    private function moveCaption()
    {
        // utminfo(func_get_args());
        $fileArray = $this->searchDownloads("srt");
        if ($fileArray !== null) {

            foreach ($fileArray as $row) {
                $key = $row['key'];
                $caption_file = $row['src'];
                $newCaption_file = __INC_WEB_CAPTION_ROOT__.'/'.$key.'.vtt';

                // if (Mediatag::$filesystem->exists($caption_file)) {
                if (!Mediatag::$filesystem->exists($newCaption_file)) {
                    if (Option::istrue('test')) {
                        $out = "<question>jSon</question>\n\t<comment>Old:".basename($caption_file)."</comment>\n\t<info>New:".basename($newJson_file).'</info>';
                        Mediatag::$output->writeln($out);
                    } else {
                        $original = file_get_contents($caption_file);
                        $vtt = 'WEBVTT'.PHP_EOL.PHP_EOL.$original;
                        // Replace microseconds separator: 00,000 -> 00.000
                        $vtt = preg_replace('#(\d{2}),(\d{3})#', '${1}.${2}', $vtt);

                        // Write the .vtt file
                        file_put_contents($newCaption_file, $vtt);
                        unlink($caption_file);
                        $out                   = '<info>'.basename($newCaption_file).' </info>';
                        Mediatag::$output->writeln($out);
                    }
                } else {
                    //$this->filesToRemove[] = $caption_file;
                    unlink($caption_file);

                    $out                   = '<question>'.basename($newCaption_file).' already exists</question>';
                    Mediatag::$output->writeln($out);
                }
            }
        }

        // }

    }



    private function moveVideo($videoInfo)
    {
        // utminfo(func_get_args());

        $old_name = $videoInfo['video_name'];
        $old_path = $videoInfo['video_path'];

        $video_name = Strings::cleanFileName($old_name);

        $new_path = Strings::after($old_path, __PLEX_DOWNLOAD__.'/');
        $new_path = Strings::before($new_path, '/');

        $video_path = str_replace('Downloads', $new_path.'/Premium', $videoInfo['video_path']);
        $video_path = str_replace('Premium/'.$new_path, 'Premium', $video_path);

        $video_path = str_replace('_', ' ', $video_path);
        if (!Mediatag::$filesystem->exists($video_path)) {
            Mediatag::$filesystem->mkdir($video_path);
        }
        $old_file = $old_path.'/'.$old_name;
        $new_file = $video_path.'/'.$video_name;

        if (!Mediatag::$filesystem->exists($new_file)) {
            if (Option::istrue('test')) {
                return "<question>Video</question>\n\t<comment>Old:".basename($old_file)."</comment>\n\t<info>New:".$new_file."</info>\n";
            }

            Mediatag::$filesystem->rename($old_file, $new_file, false);
            $this->newFiles[] = $new_file;

            return "<info>Video {$old_name} moved to  {$video_name}</info>";
        }
        if (Mediatag::$filesystem->exists($new_file)) {
            $this->filesToRemove[] = $old_file;

            return '<error>Video '.$new_file.' exists</error>';
        }
    }

    private function cleanDupeFiles()
    {
        // utminfo(func_get_args());

        $go = Chooser::changes('Remove Duplicate files');

        if (true == $go) {
            foreach ($this->filesToRemove as $file) {
                Mediatag::$output->writeLn('<info> removing file '.basename($file).'</info>');
                // Mediatag::$filesystem->remove($file);
            }
        }
    }

    private function cleanEmptyDir()
    {
        // utminfo(func_get_args());

        // $command = [
        //     '/usr/bin/find',
        //     __CURRENT_DIRECTORY__,
        //     '-mindepth',
        //     '1',
        //     '-type',
        //     'd',
        //     '-empty',
        //     '-delete',
        // ];
        // $proccess = new ExecProcess($command);
        // $proccess->run();
    }


    private function searchDownloads($type = "json")
    {
        $fileArray = [];
        switch ($type) {
            case 'json':
                $search_params = "info.json";
                $desc = "Json ";
                break;
            case 'srt':
                $search_params = "en.srt";
                $desc = "Caption ";
                break;

        }
        $file_array = Mediatag::$finder->Search(__CURRENT_DIRECTORY__, '*.'.$search_params, exit: false);

        //  utmdd($file_array);
        if ($file_array === null) {
            return null;
        }
        foreach ($file_array as $file) {


            $success = preg_match('/-(p?h?[a-z0-9]+).'.$search_params.'/', basename($file), $matches);
            if (1 === $success) {
                $key = $matches[1];
            } else {
                utmdd($matches);
            }
            // } else {
            //     continue;
            // }
            // Mediatag::$output->writeln($desc." File " . basename($file). " key " . $key);

            $fileArray[] = ["src" => $file,"key" => $key];
        }
        return $fileArray;
    }








}
