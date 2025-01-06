<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Callables\Callables;

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Modules\Filesystem\MediaFile;

trait CallableHelper
{
    private function cleanBuffer($buffer)
    {
        $buffer = str_replace(["\n", "\r"], '', $buffer);

        return $buffer;
    }

    public function Pornhub($buffer, $line_id)
    {
        $outputText = '';
        $buffer     = $this->cleanBuffer($buffer);

        PlaylistProcess::$current_key = false;
        if (str_contains($buffer, 'pc webpage')) {
            --$this->num_of_lines;
            $line_id = '<id>'.$this->num_of_lines.'</id>';

            $outputText = $line_id.' <text>Trying to download  '.$this->key.'  </text>'.\PHP_EOL;
        }

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
        $buffer = $this->cleanBuffer($buffer);

        PlaylistProcess::$current_key = $this->key;
        $outputText                   = '<download>'.$buffer.'</>';
        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/" . $this->key . ".log", $buffer . PHP_EOL);
        if (str_contains($buffer, 'Destination')) {
            $outputText = str_replace('[download]', '</text>'.\PHP_EOL.$line_id.' <text>[download]', $buffer);
            $outputText = $line_id.' <text>'.str_replace(__PLEX_DOWNLOAD__, '', $outputText).'</file>';
            $outputText = str_replace('Destination:', 'Destination:</text> <file>', $outputText);

            return $outputText.\PHP_EOL;
        }

        if (str_contains($buffer, 'already been')) {
            $outputText = $line_id.'<error> Already been downloaded </error>';

            return $outputText.\PHP_EOL;
        }

        return $outputText;
    }

    public function fixVideo($buffer, $line_id)
    {
        $buffer = $this->cleanBuffer($buffer);

        $outputText = \PHP_EOL.str_replace('[FixupM3u8]', $line_id.' <text>[FixupM3u8]', $buffer);

        $outputText = str_replace(__PLEX_DOWNLOAD__, '', $outputText);
        $outputText = str_replace('container of', 'container of</text> <file>', $outputText);
        $outputText .= '</file>';

        return $outputText.\PHP_EOL;
    }
}
