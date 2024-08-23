<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Download;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Utilities\Chooser;
use UTM\Utilities\Option;
use Mediatag\Utilities\ScriptWriter;
use Mediatag\Utilities\Strings;
use Symfony\Component\Process\Process as ExecProcess;

trait Helper
{
    public function convertVideos()
    {
        utminfo();

        $file_array = [];
        $file_array = Mediatag::$finder->Search(__CURRENT_DIRECTORY__, '*.mkv');
        if (null === $file_array) {
            return 0;
        }
        $count      = \count($file_array);
        if ($count > 0) {
            $this->textSection = Mediatag::$output->section();
            $this->barSection  = Mediatag::$output->section();
            $this->textSection->writeln('<info> Found ' . $count . ' files to convert</info>');
            foreach ($file_array as $k => $file) {
                $this->textSection->write('<comment> Converting <info>' . basename($file, '.mkv') . '</info>... </comment>');
                $this->convertVideo($file);
                $this->textSection->overwrite('<comment> finished </comment>');
            }
        }
    }

    public function jSonCache()
    {
        utminfo();

        $file_array = Mediatag::$finder->Search(__CURRENT_DIRECTORY__, '*.json');

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
        utminfo();

        foreach (Mediatag::$SearchArray as $key => $file) {
            $videoInfo = File::file($file);
            $ytdl_file = $videoInfo['video_file'] . '.ytdl';
            $temp_file = str_replace('mp4', 'temp.mp4', $videoInfo['video_file']);
            if (file_exists($ytdl_file)) {
                Mediatag::$output->writeln($ytdl_file);

                continue;
            }
            if (file_exists($temp_file)) {
                Mediatag::$output->writeln($temp_file);

                continue;
            }

            if ($this->moveJson($videoInfo)) {
                $out = $this->moveVideo($videoInfo);
            } else {
                $json_file    = basename($videoInfo['video_file']);
                $success      = preg_match('/-(p?h?[a-z0-9]+).mp4/', basename($json_file), $matches);
                if (1 === $success) {
                    $json_key = $matches[1];
                } else {
                    continue;
                }

                $newJson_file = __JSON_CACHE_DIR__ . '/' . $json_key . '.info.json';
                if (Mediatag::$filesystem->exists($newJson_file)) {
                    $out = $this->moveVideo($videoInfo);
                } else {
                    $out = '<error>no json found for ' . $videoInfo['video_name'] . " </error>\n";
                }
            }

            Mediatag::$output->writeln($out);
        }

        if (\count($this->newFiles) > 0) {
            $ScriptWriter = new ScriptWriter('addedFiles.sh', __PLEX_HOME__ . '/Pornhub');
            $ScriptWriter->addCmd('update', ['-f']);
            $ScriptWriter->addFileList($this->newFiles);
            $ScriptWriter->write();
        }

        if (\count($this->filesToRemove) > 0) {
            $this->cleanDupeFiles();
        }

        if (!Option::istrue('test')) {
            $this->cleanEmptyDir();
        }

        Mediatag::$output->writeln('Done');
    }

    private function moveJson($videoInfo)
    {
        utminfo();

        $old_name     = $videoInfo['video_name'];
        $old_path     = $videoInfo['video_path'];
        $json_key     = '';
        $json_file    = $old_path . '/' . basename($old_name, 'mp4') . 'info.json';
        if (Mediatag::$filesystem->exists($json_file)) {
            $success = preg_match('/-(p?h?[a-z0-9]+).info.json/', basename($json_file), $matches);
            if (1 === $success) {
                $json_key = $matches[1];
            } else {
                utmdd($matches);
            }
        }

        $newJson_file = __JSON_CACHE_DIR__ . '/' . $json_key . '.info.json';

        if (Mediatag::$filesystem->exists($json_file)) {
            if (!Mediatag::$filesystem->exists($newJson_file)) {
                if (Option::istrue('test')) {
                    $out = "<question>jSon</question>\n\t<comment>Old:" . basename($json_file) . "</comment>\n\t<info>New:" . basename($newJson_file) . '</info>';
                    Mediatag::$output->writeln($out);
                } else {
                    Mediatag::$filesystem->rename($json_file, $newJson_file, false);
                }
            } else {
                $this->filesToRemove[] = $json_file;
                $out                   = '<question>' . basename($newJson_file) . ' already exists</question>';
                Mediatag::$output->writeln($out);
            }

            return true;
        }

        return false;
    }

    private function moveVideo($videoInfo)
    {
        utminfo();

        $old_name   = $videoInfo['video_name'];
        $old_path   = $videoInfo['video_path'];

        $video_name = Strings::cleanFileName($old_name);

        $video_path = str_replace('Downloads', 'Pornhub/Premium', $videoInfo['video_path']);
        $video_path = str_replace('_', ' ', $video_path);
        if (!Mediatag::$filesystem->exists($video_path)) {
            Mediatag::$filesystem->mkdir($video_path);
        }
        $old_file   = $old_path . '/' . $old_name;
        $new_file   = $video_path . '/' . $video_name;
        if (!Mediatag::$filesystem->exists($new_file)) {
            if (Option::istrue('test')) {
                return "<question>Video</question>\n\t<comment>Old:" . basename($old_file) . "</comment>\n\t<info>New:" . basename($new_file) . "</info>\n";
            }

            Mediatag::$filesystem->rename($old_file, $new_file, false);
            $this->newFiles[] = $new_file;

            return "<info>Video {$old_name} moved to  {$video_name}</info>";
        }
        if (Mediatag::$filesystem->exists($new_file)) {
            $this->filesToRemove[] = $old_file;

            return "<error>Video {$new_file} exists</error>";
        }
    }

    private function cleanDupeFiles()
    {
        utminfo();

        $go = Chooser::changes(Mediatag::$input, Mediatag::$output, 'Remove Duplicate files');

        if (true == $go) {
            foreach ($this->filesToRemove as $file) {
                Mediatag::$output->writeLn('<info> removing file ' . basename($file) . '</info>');
                Mediatag::$filesystem->remove($file);
            }
        }
    }

    private function cleanEmptyDir()
    {
        utminfo();

        $command  = [
            '/usr/bin/find',
            __CURRENT_DIRECTORY__,
            '-mindepth',
            '1',
            '-type',
            'd',
            '-empty',
            '-delete',
        ];
        $proccess = new ExecProcess($command);
        $proccess->run();
    }
}
