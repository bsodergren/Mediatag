<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Show;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\TagBuilder\Meta\Reader as metaReader;
use Mediatag\Process\DB\Process as DBProcess;
use UTM\Utilities\Option;
use Mediatag\Utilities\ScriptWriter;
use Symfony\Component\Console\Helper\ProgressBar;

trait Helper
{
    public function findMissing($options)
    {
        utminfo(func_get_args());

        //   utmdd([__METHOD__,$options]);
        $missingTaglist = $options[0];

        if ('all' == $missingTaglist) {
            $missing_tags = __META_TAGS__;
        } else {
            $missing_tags = explode(',', $missingTaglist);
        }

        $filelist_array = $this->VideoList['file'];
        $count          = \count($filelist_array);

        Mediatag::$output->writeln('<info>Finding missing tags</info>');
        //        ProgressBar::setFormatDefinition('custom', '<info>%current%/%max%</info> -- <comment>%message%  (%filename%)</comment>');
        $progressBar    = new ProgressBar(Mediatag::$output, $count);
        $progressBar->setBarWidth(400);
        //      $progressBar->setFormat('custom');
        $progressBar->start();
        //    $progressBar->setMessage('finding missing tags...');
        //   $progressBar->setMessage('', 'filename');
        foreach ($filelist_array as $key => $fileinfo) {
            //       $progressBar->setMessage($fileinfo['video_name'], 'filename');

            // $result = (new VideoData())->getvideoData($fileinfo)->getTags();

            $meta    = new metaReader($fileinfo);
            $tagList = $meta->getTagArray();

            $progressBar->advance();

            foreach ($missing_tags as $missing_tag) {
                if (!\array_key_exists($missing_tag, $tagList)) {
                    $this->missing[$missing_tag][$key] = $filelist_array[$key];
                } else {
                    if ('' == $tagList[$missing_tag]) {
                        $this->missing[$missing_tag][$key] = $filelist_array[$key];
                    }
                }
            }
        }
        $progressBar->finish();
        Mediatag::$output->writeln('');

        if (\count($this->missing) > 0) {
            foreach ($this->missing as $tag => $missing_file) {
                $obj = new ScriptWriter('missing_' . $tag . '.sh', __CURRENT_DIRECTORY__);
                $obj->addCmd('update', ['-o', $tag, '-f']);
                foreach ($missing_file as $k => $file) {
                    $obj->addFile($file['video_file'], false);
                }
                $obj->write();
            }
            Mediatag::$output->writeln('Wrote new script');
        }
    }

    public function newFiles()
    {
        utminfo(func_get_args());

        $DBProcess    = DBProcess::getNewFiles(Mediatag::$SearchArray, Mediatag::$input, Mediatag::$output);
        $ScriptWriter = new ScriptWriter('newFiles.sh', __CURRENT_DIRECTORY__);
        $ScriptWriter->addCmd('update', ['-U', '-f']);
        $ScriptWriter->addFileList($DBProcess['New']);
        //     $ScriptWriter->addCmd('db', ['-f']);
        //     $ScriptWriter->addFileList($DBProcess['New']);
        $ScriptWriter->write();

        exit;
    }

    public function createPlaylist()
    {
        utminfo(func_get_args());

        $playlist_file = Option::getValue('playlist');
        foreach (Mediatag::$SearchArray as $filename) {
            $success = preg_match('/-(p?h?[a-z0-9]{6,}).mp4/i', $filename, $matches);
            if (1 == $success) {
                $video_keys[] = $matches[1];
            }
        }
        $file_string   = '';
        foreach ($video_keys as $v => $key) {
            $file_string .= 'https://www.pornhub.com/view_video.php?viewkey=' . $key . \PHP_EOL;
        }

        Filesystem::writeFile($playlist_file, $file_string);
    }

    public function oldfiles()
    {
        utminfo(func_get_args());

        // $playlist_file = Option::getValue('playlist');
        foreach (Mediatag::$SearchArray as $filename) {
            $success = preg_match('/-(p?h?[a-z0-9]{6,}).mp4/i', $filename, $matches);
            if (0 == $success) {
                $video_keys[] = $filename;
            }
        }
        utmdd([__METHOD__, $video_keys]);
        // $file_string   = '';
        // foreach ($video_keys as $v => $key) {
        //     $file_string .= 'https://www.pornhub.com/view_video.php?viewkey='.$key.\PHP_EOL;
        // }

        // Filesystem::writeFile($playlist_file, $file_string);
    }

    public function duplicateFiles()
    {
        utminfo(func_get_args());

        utmdd([__METHOD__, Mediatag::$SearchArray]);
    }
}
