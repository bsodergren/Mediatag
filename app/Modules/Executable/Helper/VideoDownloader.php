<?php

namespace Mediatag\Modules\Executable\Helper;

use const PHP_EOL;

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\Callbacks\traits\CallbackCommon;
use Mediatag\Modules\Executable\Callbacks\traits\DownloadStrings;
use Mediatag\Modules\Executable\Callbacks\traits\YtdlpCallBacks;
use Mediatag\Modules\Executable\Helper\traits\FilterMethods;
use Mediatag\Modules\Executable\MediatagExec;
use Mediatag\Modules\Filesystem\MediaFile;

use function array_key_exists;

class VideoDownloader
{
    use FilterMethods;

    public $obj;

    public $DownloadableIds = [];

    public $model_hub = '';

    public $premium = '';

    public $key = '';

    public $KeyPrefix = '';

    public $num_of_lines = 0;

    public static $VideoLogFile = 'video_download.log';

    public $registeredbufferFilters = [];

    public function __construct() {}

    public static function log($message, $context = [], $file = '')
    {
        if ($file == '') {
            $file = self::$VideoLogFile;
        }

        Mediatag::file($file, $message, $context);
    }

    public static function logBuffer($message, $buffer, $file = '')
    {
        $buffer = MediatagExec::cleanBuffer($buffer);

        if (is_string($buffer) && ! str_contains($buffer, '%')) {
            self::log($message, $buffer, $file);
        }
    }

    public function downloadCallback($type, $buffer)
    {
        // $buffer = MediatagExec::cleanBuffer($buffer);

        $ConsoleCmd = 'writeln';
        $outputText = '';
        $line_id    = '<id>' . $this->num_of_lines . '</id>';
        if (preg_match('/(ERROR|\[.*\]):?\s+([a-z0-9]+):\s+(.*)/', $buffer, $matches)) {
            if (array_key_exists(2, $matches)) {
                if ($matches[2] != '') {
                    $this->key = $matches[2];
                }
            }
        }

        // if (!str_contains($buffer, '[download]') && !str_contains($buffer, 'ETA')) {
        //     // UTMlog::Logger('Ph Download', $buffer);
        // }
        // // UTMlog::Logger('Ph Download', $buffer);

        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/" . $this->key . ".log", $buffer . PHP_EOL);
        if (! isset($this->registeredbufferFilters)) {
            Mediatag::error('No Filters Set');
        }
        foreach ($this->registeredbufferFilters as $filter => $properties) {
            $ConsoleCmd   = 'writeln';
            $searchCmd    = null;
            $OutputMethod = null;
            $outputText   = '';

            if (array_key_exists('search', $properties)) {
                if (! is_array($properties['search'])) {
                    $searchCmd = $properties['search'];
                } else {
                    $pattern = $properties['search']['pattern'];
                    $match   = $properties['search']['match'];
                    $matched = preg_match($pattern, $buffer, $matches);
                    if ($matched) {
                        if (array_key_exists($match, $matches)) {
                            $this->num_of_lines = $matches[$match];
                        }
                    }
                }
            }

            if (array_key_exists('ConsoleCmd', $properties)) {
                $ConsoleCmd = $properties['ConsoleCmd'];
            }

            if (array_key_exists('OutputMethod', $properties)) {
                $OutputMethod = $properties['OutputMethod'];

                if ($OutputMethod === null) {
                    continue;
                }

                $success = $searchCmd($buffer, $filter);

                if ($success) {
                    // utmdump([$searchCmd, $buffer, $filter, $OutputMethod]);
                    if (is_array($OutputMethod)) {
                        foreach ($OutputMethod as $func => $value) {
                            if (method_exists($this, $func)) {
                                $args = [$buffer, $line_id];
                                if (is_array($value)) {
                                    if (array_key_exists('msg', $value)) {
                                        $args[]     = $value['msg'];
                                        $outputText = call_user_func_array([$this, $func], $args);
                                    } elseif (array_key_exists('args', $value)) {
                                        $args = [$value['args']];
                                        call_user_func_array([$this, $func], $args);
                                    }
                                }
                            }
                        }
                    } else {
                        if (method_exists($this, $OutputMethod)) {
                            $outputText = $this->$OutputMethod($buffer, $line_id);
                        }
                    }
                    if ($outputText != '') {
                        // if (!str_contains($outputText, '<download>')) {
                        //     utmdump([$ConsoleCmd, $outputText]);
                        // }
                        VideoDownloader::LogBuffer($ConsoleCmd . ' -> ' . $outputText, $buffer, '/buffer/' . $this->key);
                        Mediatag::$Console->$ConsoleCmd($outputText);

                        return true;
                    }

                    continue;
                }
            }
            // utmdd([$filter, $properties, $searchCmd, $OutputMethod, $ConsoleCmd, $outputText]);
        }

        // switch ($buffer) {
        //     case str_starts_with($buffer, '[PornHubPlaylist]'):
        //         $match = preg_match(, $buffer, $output_array);
        //         if ($match == true) {
        //             $this->num_of_lines = $output_array[1];
        //         }
        //         $ConsoleCmd = 'writeln';
        //         // utmdump($output_array);
        //         break;

        //     case str_starts_with($buffer, '[PornHub]'):
        //         $outputText = $this->Pornhub($buffer, $line_id);
        //         $ConsoleCmd = 'writeln';
        //         break;

        //     case str_contains($buffer, 'Interrupted by user'):
        //         $this->error($buffer, $line_id, 'cancelled');
        //         $ConsoleCmd = 'writeln';

        //         return 0;

        //     case str_contains($buffer, 'private.'):
        //         $outputText = $this->error($buffer, $line_id, 'private');
        //         $this->updateIdList(PlaylistProcess::DISABLED);

        //         break;

        //     case str_contains($buffer, 'restriction'):
        //         $outputText = $this->error($buffer, $line_id, 'is restricted ');
        //         $this->updateIdList(PlaylistProcess::DISABLED);
        //         $ConsoleCmd = 'writeln';
        //         break;

        //     case str_contains($buffer, 'disabled'):
        //         $outputText = $this->error($buffer, $line_id, ' has been disabled ');
        //         $this->updateIdList(PlaylistProcess::DISABLED);
        //         $ConsoleCmd = 'writeln';
        //         break;

        //     case str_contains($buffer, 'HTTPError'):
        //         $outputText = $this->error($buffer, $line_id, 'NOT FOUND');

        //         // $this->premiumIds[] = $this->key;

        //         $this->updateIdList(PlaylistProcess::DISABLED);
        //         $ConsoleCmd = 'writeln';
        //         break;

        //     case str_contains($buffer, 'Upgrade now'):
        //         $outputText = $this->error($buffer, $line_id, ' Premium Video');
        //         $this->updatePlaylist('premium');
        //         $this->premiumIds[] = $this->key;
        //         $ConsoleCmd         = 'writeln';
        //         break;

        //     case str_contains($buffer, 'encoded url'):
        //         $outputText = $this->error($buffer, $line_id, 'ModelHub Video');
        //         // $this->updatePlaylist('modelhub');
        //         // $this->updateIdList(PlaylistProcess::MODELHUB);
        //         $ConsoleCmd = 'writeln';
        //         break;

        //     case str_starts_with($buffer, '[info]'):
        //         if ($this->downloadFiles === false) {
        //             $outputText = $this->downloadableIds($buffer);
        //         }
        //         $ConsoleCmd = 'writeln';
        //         break;

        //     case str_contains($buffer, '[download]'):
        //         $outputText = $this->downloadVideo($buffer, $line_id);
        //         $ConsoleCmd = 'write';
        //         break;

        //     case str_contains($buffer, '[FixupM3u8]'):
        //         $outputText = $this->fixVideo($buffer, $line_id);
        //         $ConsoleCmd = 'writeln';
        //         break;

        //     case str_contains($buffer, 'ERROR'):
        //         $outputText = $this->error($buffer, $line_id, 'Uncaught Error </>  <comment>' . $buffer . '</comment><error>');
        //         // $this->updatePlaylist('error');
        //         // $this->updateIdList(PlaylistProcess::ERRORIDS);
        //         $ConsoleCmd = 'writeln';
        //         break;
        // }
    }
}
