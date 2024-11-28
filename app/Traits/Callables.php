<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Nette\Utils\Strings;
use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use UTM\Bundle\Monolog\UTMLog;
use Mediatag\Traits\CallableHelper;
use Symfony\Component\Process\Process;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Commands\Playlist\Process as PlaylistProcess;

trait Callables
{
    use CallableHelper;

    public function splitFileOutput($type, $buffer)
    {
        Mediatag::$output->writeln($buffer);
        echo $buffer;
        //$this->Console->writeln($buffer);

    }
    public function Output($type, $buffer)
    {
        echo $buffer;
    }
    public function ReadOutput($type, $buffer)
    {
        $this->stdout .= $buffer;
    }

    public function ReadMetaOutput($type, $buffer)
    {
                    // MediaFile::file_append_file(__LOGFILE_DIR__ . "/metadata/" . $this->video_key . ".log", $buffer . PHP_EOL);

        if (Process::ERR === $type) {
            $this->errors .= $buffer;
        } else {
            $this->getMetaValue($buffer);
        }
        $this->stdout .= $buffer;
    }

    public function ProcessOutput($type, $buffer)
    {
        if (Process::ERR === $type) {
            echo 'ERR > ' . $buffer;
        } else {
            echo 'OUT > ' . $buffer;
        }
    }

    public function WriteMetaOutput($type, $buffer)
    {
        if (Process::ERR === $type) {
            $this->errors .= $buffer;
            // UTMlog::logError('Writing Metadata', $buffer);
            // UTMlog::logError('Writing Metadata', $this->video_file);
        } else {
            if (str_contains($buffer, 'error')) {
                $this->errors .= $buffer;
                // UTMlog::logError('Writing Metadata', $buffer);
                // UTMlog::logError('Writing Metadata', $this->video_file);
            } elseif (str_contains($buffer, 'warning')) {
                // UTMlog::logWarning('Writing Metadata', $buffer);
                // UTMlog::logWarning('Writing Metadata', $this->video_file);
            } else {
                $out = $buffer;
                if (str_contains($buffer, "\r")) {
                    $out = Strings::before($buffer, "\r");
                }

                $this->Display->processOutput->overwrite($out);
            }
        }
    }

    public function parseArchive($line)
    {

        $key = Strings::after($line, ' ');
        if (!\array_key_exists($key, $this->json_Array)) {
            return $line;
        }

        return false;
    }

    public function filemap($line)
    {

        if ('' != $line) {
            $ph_id = Strings::after($line, '=');
            if (str_contains($ph_id, '&')) {
                $ph_id = Strings::before($ph_id, '&');
            }
            $file_map[$ph_id] = [
                'url' => Strings::after($line, '-> '),
                'old' => Strings::before($line, ' ->'),
            ];

            return $file_map;
        }

        return false;
    }

    public function getpremiumListIds($line)
    {

        $ph_id = Strings::after($line, '=');
        if (str_contains($ph_id, '&')) {
            $ph_id = Strings::before($ph_id, '&');
        }

        return $ph_id;
    }

    public function compactPlaylist($line)
    {

        $ph_id = Strings::after($line, '=');
        if (str_contains($ph_id, '&')) {
            $ph_id = Strings::before($ph_id, '&');
        }

        if (!\in_array($ph_id, $this->ids)) {
            if (str_contains($line, 'view_video.php')) {
                return $line;
            }
        }

        return false;
    }

    public function studioList($line)
    {
        // utminfo(func_get_args());

        if ('' != $line) {
            $studioReplacement = '';
            $studio            = $line;
            if (str_contains($line, ':')) {
                $studio            = Strings::before($line, ':');
                $studioReplacement = ':' . Strings::after($line, ':');
            }

            return $studio . $studioReplacement;
        }

        return false;
    }

    public function studioPaths($line)
    {
        // utminfo(func_get_args());

        if ('' != $line) {
            if (!str_contains($line, ':')) {
                $line = $line . ':' . $line;
            }

            $studio_match = Strings::before($line, ':');
            $studio_match = strtolower(str_replace(' ', '_', $studio_match));

            return [
                $studio_match => Strings::after($line, ':'),
            ];
        }

        return false;
    }

    public function toList($line)
    {
        // utminfo(func_get_args());

        if ('' != $line) {
            $Replacement = $line;
            $match       = $line;
            if (str_contains($line, ':')) {
                $match       = Strings::before($line, ':');
                $Replacement = Strings::after($line, ':');
                if ('' == $Replacement) {
                    $Replacement = null;
                }
            }
            $key = strtolower($match);
            $key = str_replace(' ', '_', $key);
            $key = str_replace('+', '', $key);
            $key = str_replace('(', '', $key);
            $key = str_replace(')', '', $key);

            return [$key => $Replacement];
        }

        return false;
    }

    public function toArray($line)
    {
        // utminfo(func_get_args());

        if ('' != $line) {
            $Replacement = null;
            $match       = $line;
            if (str_contains($line, ':')) {
                $match       = Strings::before($line, ':');
                $Replacement = Strings::after($line, ':');
            }
            $key = strtolower($match);
            $key = str_replace(' ', '_', $key);
            $key = str_replace('+', '', $key);
            $key = str_replace('(', '', $key);
            $key = str_replace(')', '', $key);

            return [$key => $Replacement];
        }

        return false;
    }

    public function watchlistCallback($type, $buffer)
    {
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
            $this->key = str_replace("\n", '', $buffer);
            $this->updatePlaylist($this->pltype);
        }
        // $this->Console->writeln($this->key );
    }


    public function downloadJsonCallback($type, $buffer)
    {
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

        $buffer = str_replace("\n", '', $buffer);
        switch ($buffer) {
            case str_contains($buffer, '[info]'):
                //

                if (str_contains($buffer, 'as JSON')) {

                    $this->yt_json_string = $buffer;
                }
                break;
        }
    }


    public function downloadCallback($type, $buffer)
    {


        $outputText = '';
        $line_id    = '<id>' . $this->num_of_lines . '</id>';
        if (preg_match('/(ERROR|\[.*\]):?\s+([a-z0-9]+):\s+(.*)/', $buffer, $matches)) {
            if (\array_key_exists(2, $matches)) {
                if ('' != $matches[2]) {
                    $this->key = $matches[2];
                }
            }
        }

        $buffer = str_replace("\n", '', $buffer);
        // if (!str_contains($buffer, '[download]') && !str_contains($buffer, 'ETA')) {
        //     // UTMlog::Logger('Ph Download', $buffer);
        // }
        //// UTMlog::Logger('Ph Download', $buffer);

        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/" . $this->key . ".log", $buffer . PHP_EOL);

        switch ($buffer) {
            case str_starts_with($buffer, '[PornHub]'):
                $outputText = $this->Pornhub($buffer, $line_id);
                break;

            case str_contains($buffer, 'Interrupted by user'):

                $this->error($buffer, $line_id, 'cancelled');
                return 0;

            case str_contains($buffer, 'private.'):

                $outputText = $this->error($buffer, $line_id, 'private');
                // $this->updateIdList(PlaylistProcess::DISABLED);

                break;

            case str_contains($buffer, 'restriction'):
                $outputText = $this->error($buffer, $line_id, 'is restricted ');
                // $this->updateIdList(PlaylistProcess::DISABLED);
                break;

            case str_contains($buffer, 'disabled'):

                $outputText = $this->error($buffer, $line_id, ' has been disabled ');
                // $this->updateIdList(PlaylistProcess::DISABLED);

                break;

            case str_contains($buffer, 'HTTPError'):

                $outputText = $this->error($buffer, $line_id, 'NOT FOUND');

                $this->premiumIds[] = $this->key;

                // $this->updateIdList(PlaylistProcess::DISABLED);

                break;

            case str_contains($buffer, 'Upgrade now'):

                $outputText = $this->error($buffer, $line_id, ' Premium Video');
                // $this->updatePlaylist('premium');
                // $this->premiumIds[] = $this->key;

                break;

            case str_contains($buffer, 'encoded url'):
                $outputText = $this->error($buffer, $line_id, 'ModelHub Video');
                // $this->updatePlaylist('modelhub');
                // $this->updateIdList(PlaylistProcess::MODELHUB);

                break;

            case str_starts_with($buffer, '[info]'):
                if ($this->downloadFiles === false) {
                    $outputText = $this->downloadableIds($buffer);
                }
                break;

            case str_contains($buffer, '[download]'):
                $outputText = $this->downloadVideo($buffer, $line_id);

                break;

            case str_contains($buffer, '[FixupM3u8]'):


                $outputText = $this->fixVideo($buffer, $line_id);

                break;

            case str_contains($buffer, 'ERROR'):

                $outputText = $this->error($buffer, $line_id, "Uncaught Error </>  <comment>" . $buffer . '</comment><error>');
                // $this->updatePlaylist('error');
                // $this->updateIdList(PlaylistProcess::ERRORIDS);

                break;
        }

        // if (Option::istrue('debug')) {
        //     $style     = 'info';
        //     $style_end = 'info';
        //     if (preg_match('/(ERROR):(.*)/', $buffer, $matches)) {
        //         $style     = 'fg=bright-magenta';
        //         $style_end = '';
        //     }
        //     $outputText = __LINE__ . '<comment>' . $this->num_of_lines . '</comment> <' . $style . '>' . $buffer . '</' . $style_end . '>' ;
        // }
        if ($outputText != '') {
            $this->Console->write($outputText);
        }
    }
}
