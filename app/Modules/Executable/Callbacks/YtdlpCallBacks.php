<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Callbacks;

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Modules\Filesystem\MediaFile;

trait YtdlpCallBacks
{
    use CallbackCommon;

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
        if (null !== $this->key) {
            // utmdump($buffer,$this->key);

            if (str_contains($buffer, $this->key.': Downloading')) {
                --$this->num_of_lines;
                $line_id = '<id>'.$this->num_of_lines.'</id>';

                $outputText = $line_id.' <text>Trying to download  '.$this->key.'  </text>'.\PHP_EOL;
            }
        }

        // utmdump([__LINE__,$outputText]);
        return $outputText;
    }

    public function watchlistCallback($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);

        if (str_contains($buffer, '[PLAYLIST]')) {
            $this->Console->writeln($buffer);
        // if (preg_match('/(ERROR|\[.*\]):?\s+([a-z0-9]+):?\s?+(.*)?/', $buffer, $matches)) {
        //     if (\array_key_exists(2, $matches)) {
        //         if ('' != $matches[2]) {
        //             $outputText                   = '  <id> '.$matches[2].' cancelled </id>';
        //             $this->Console->writeln($outputText);

        //         }
        //     }
        // }
        } else {
            $this->key = $buffer;
            $this->updatePlaylist($this->pltype);
        }
        // $this->Console->writeln($this->key );
    }

    public function downloadJsonCallback($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);

        // $outputText = '';
        // $line_id    = \PHP_EOL . '<id>' . $this->num_of_lines . '</id>';
        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/json" . ".log", $buffer . PHP_EOL);

        if (preg_match('/(ERROR|\[.*\]):?\s+([a-z0-9]+):\s+(.*)/', $buffer, $matches)) {
            if (\array_key_exists(2, $matches)) {
                if ('' != $matches[2]) {
                    $this->key = $matches[2];
                }
            }
        }

        switch ($buffer) {
            case str_contains($buffer, '[info]'):
                if (str_contains($buffer, 'as JSON')) {
                    $this->yt_json_string = $buffer;
                }
                break;
        }
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
            utmdump([__LINE__, $buffer]);

            $outputText = str_replace('[download]', '</text>'.$line_id.' <text>[download]', $buffer);
            utmdump([__LINE__, $outputText]);

            $outputText = '<text>'.str_replace(__PLEX_DOWNLOAD__, '', $outputText).'</file>'.\PHP_EOL;
            utmdump([__LINE__, $outputText]);

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
        $outputText = '<download>'.$buffer.'</>';

        // utmdump([__LINE__,$outputText]);
        return $outputText;
    }

    public function fixVideo($buffer, $line_id, $key = 'FixupM3u8')
    {
        $buffer = $this->cleanBuffer($buffer);

        $outputText = \PHP_EOL.str_replace('['.$key.']', $line_id.' <text>['.$key.']', $buffer);

        $outputText = str_replace(__PLEX_DOWNLOAD__, '', $outputText);

        if ('FixupM3u8' == $key) {
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
