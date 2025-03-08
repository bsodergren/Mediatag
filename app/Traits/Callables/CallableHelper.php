<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Callables;

/*
 * Command like Metatag writer for video files.
 */

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Modules\Filesystem\MediaFile;

trait CallableHelper
{
    public function cleanBuffer($buffer)
    {
        $buffer = str_replace(["\n", "\r"], '', $buffer);

        return $buffer;
    }

    public function Pornhub($buffer, $line_id)
    {
        $outputText = '';
        $buffer     = $this->cleanBuffer($buffer);

        PlaylistProcess::$current_key = false;
        if (str_contains($buffer, 'webpage')) {
            --$this->num_of_lines;
            $line_id = '<id>'.$this->num_of_lines.'</id>';

            $outputText = $line_id.' <text>Trying to download  '.$this->key.'  </text>'.\PHP_EOL;
        }

        return $outputText;
    }

    public function NubilesPorn($buffer, $line_id)
    {
        $outputText = '';
        $buffer     = $this->cleanBuffer($buffer);

        PlaylistProcess::$current_key = false;
        if($this->key !== null){
          
        //utmdump($buffer,$this->key);

        if (str_contains($buffer, $this->key.': Downloading')) {
            --$this->num_of_lines;
            $line_id = '<id>'.$this->num_of_lines.'</id>';

            $outputText = $line_id.' <text>Trying to download  '.$this->key.'  </text>'.\PHP_EOL;
        }
    }
    // utmdump([__LINE__,$outputText]);
        return $outputText;
    }

    public function error($buffer, $line_id, $error)
    {
        $buffer = $this->cleanBuffer($buffer);

        $outputText                   = '';
        PlaylistProcess::$current_key = false;
        $outputText                   = $line_id.'  <error> '.$this->key.' '.$error.' </error>';
        // $this->Console->writeln($outputText);
        // $this->updateIdList(PlaylistProcess::DISABLED);
        // utmdump([__LINE__,$outputText]);
        return $outputText.\PHP_EOL;
    }

    public function downloadableIds($buffer)
    {
        $buffer = $this->cleanBuffer($buffer);

        $outputText = '';

        if (str_contains($buffer, 'Downloading')) {
            if (str_contains($buffer, $this->key)) {
                $outputText = "\t".' <file>file '.$this->key.' is downloadable </file>';
                $this->updatePlaylist('watchlater', 'trimmed_list.txt');
                $this->DownloadableIds[] = $this->key;
            }
        }

        return $outputText.\PHP_EOL;
    }

    public function downloadVideo($buffer, $line_id)
    {
        // $buffer = $this->cleanBuffer($buffer);

        PlaylistProcess::$current_key = $this->key;
        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/" . $this->key . ".log", $buffer . PHP_EOL);

        if (str_contains($buffer, 'Destination')) {
            utmdump([__LINE__,$buffer]);

            $outputText = str_replace('[download]', '</text>'.$line_id.' <text>[download]', $buffer);
            utmdump([__LINE__,$outputText]);

            $outputText = '<text>'.str_replace(__PLEX_DOWNLOAD__, '', $outputText).'</file>'.\PHP_EOL;
            utmdump([__LINE__,$outputText]);

            $outputText = str_replace('Destination:', 'Destination:</text> <file>', $outputText);
            // utmdump([__LINE__,$outputText]);
            return $outputText;
        }

        if (str_contains($buffer, 'already been')) {
            $outputText = $line_id.'<error>'.$this->key.' Already been downloaded </error>'.\PHP_EOL;
            // utmdump([__LINE__,$outputText]);
            return $outputText;
        }
        if (str_contains($buffer, 'Got error')) {
            $outputText = \PHP_EOL.'<error>'.$buffer.'</error>';
            return $outputText;


        }
                $outputText                   = '<download>'.$buffer.'</>';

        // utmdump([__LINE__,$outputText]);
        return $outputText;
    }

    public function fixVideo($buffer, $line_id, $key = 'FixupM3u8')
    {
        $buffer = $this->cleanBuffer($buffer);

        $outputText = \PHP_EOL.str_replace('['.$key.']', $line_id.' <text>['.$key.']', $buffer);

        $outputText = str_replace(__PLEX_DOWNLOAD__, '', $outputText);


        if($key == 'FixupM3u8'){

            $outputText = str_replace('container of', 'container of</text> <file>', $outputText);
        } else {
            // [EmbedThumbnail] mutagen: Adding thumbnail to "/media/Videos/Plex/XXX/Downloads/Studios/NA/Stepsisters_Crush_-_S6_-E5-207134.mp4"

            $outputText = str_replace('Adding thumbnail to', 'Adding thumbnail to</text> <file>', $outputText);
        }
        $outputText .= '</file>';
        // utmdump($outputText);

        return $outputText.\PHP_EOL;
    }
}
