<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Database\StorageDB;
use Mediatag\Modules\Display\ConsoleOutput;
use Mediatag\Modules\Display\Display;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFinder as Finder;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function define;
use function defined;
use function is_array;

abstract class Mediatag extends MediaCommand
{
    public $commandList = [];

    public static $SearchArray = [];

    public static $finder;

    public static $Cursor;

    public static $filesystem;

    public static $Console;

    public static $log;

    public static $Display;

    public static $dbconn;

    public $video_name;

    public $videoArray;

    public static $output;

    public $command;

    public static $input;

    public $helper;

    public $actions;

    public $default = [
        'exec'  => null,
        'print' => null,
    ];

    public static $amateurFile = __DATA_MAPS__ . '/Amateur.txt';

    public static $channelFile = __DATA_MAPS__ . '/Channels.txt';

    public static $Storage;

    public static $IoStyle;

    public static $Io;

    public static $tmpText;

    public static $ProcessHelper;

    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null, $args = null)
    {
        self::boot($input, $output, $args);
    }

    public static function __callStatic($method, $args): string
    {
        // utminfo([self::$index++ => [__FILE__,__LINE__,__METHOD__]]);

        if ($method == 'GetApp') {
            if (file_exists(CONFIG['ATOMICPARSLEY'])) {
                return CONFIG['ATOMICPARSLEY'];
            }

            exit(CONFIG['ATOMICPARSLEY'] . ' does not exist');
        }

        if (method_exists(self::$log, $method)) {
            if ($method == 'file') {
                if (array_key_exists(2, $args)) {
                    if (! is_array($args[2])) {
                        $tmp = [$args[2]];
                        unset($args[2]);
                        $args[2] = $tmp;
                    }
                }
            } else {
                if (array_key_exists(1, $args)) {
                    if (! is_array($args[1])) {
                        $args[1] = [$args[1]];
                        $args[0] = $args[0] . ' {0}';
                    }
                }
            }

            self::$log->$method(...$args);

            // return '';
        } else {
            self::error($method . ' in ' . get_class(self::$log) . ' does not exist');
        }

        return '';
    }

    public function boot(?InputInterface $input = null, ?OutputInterface $output = null, $options = null)
    {
        if (! defined('__CURRENT_DIRECTORY__')) {
            define('__CURRENT_DIRECTORY__', getcwd());
        }

        self::$input  = $input;
        self::$output = $output;

        // $this->loadStyles($input, $output);

        $this->command = self::getDefaultName();

        MediaCache::init(self::$input, self::$output);
        Option::init(self::$input, $options);

        self::$Cursor  = new Cursor(self::$output);
        self::$Console = new ConsoleOutput(self::$output, self::$input);
        self::$Display = new Display(self::$output);
        self::$dbconn  = new StorageDB;

        self::$finder     = new Finder;
        self::$filesystem = new Filesystem;

        self::notice('Current Directory {0}', [__CURRENT_DIRECTORY__]);
        self::$finder->defaultCmd = $this->command;

        if (! Option::isTrue('USE_SEARCH')) {
            self::$SearchArray = self::$finder->ExecuteSearch();

            // utmdd(self::$SearchArray);
            if (Option::isTrue('numberofFiles') == true) {
                $this->getNumberofFiles();
                exit;
            }
        }

        self::$Storage = new Storage;
        if (isset($this->useFuncs)) {
            foreach ($this->useFuncs as $method) {
                if (method_exists($this, $method)) {
                    $this->$method();
                }
            }
        }
    }

    public function process()
    {
        // utminfo([self::$index++ => [__FILE__,__LINE__,__METHOD__]]);

        $ClassCmds = $this->runCommand();
        self::debug('Running command {0}', [$this]);
        foreach ($ClassCmds as $cmd => $option) {
            if (method_exists($this, $cmd)) {
                self::$log->notice('Running command {0}', [$cmd]);

                $this->{$cmd}($option);
            } else {
                self::$output->writeln('<info>' . $cmd . ' doesnt exist</info>');

                return 0;
            }
        }
    }

    public static function App(): string
    {
        // utminfo([self::$index++ => [__FILE__,__LINE__,__METHOD__]]);

        if (file_exists(CONFIG['ATOMICPARSLEY'])) {
            return CONFIG['ATOMICPARSLEY'];
        }

        exit(CONFIG['ATOMICPARSLEY'] . ' does not exist');
    }

    public function getVideoArray()
    {
        // utminfo([self::$index++ => [__FILE__,__LINE__,__METHOD__]]);

        $file_array               = self::$SearchArray;
        $this->videoArray['file'] = [];
        $this->videoArray['dupe'] = [];
        $count                    = count($file_array);

        self::$output->writeln('<info>Getting Video array</info>');
        foreach ($file_array as $__ => $file) {
            $fs        = new File($file);
            $videoData = $fs->get();

            if (! array_key_exists($videoData['video_key'], $this->videoArray['file'])) {
                $meta_key = 'file';
            } else {
                $meta_key = 'dupe';
            }
            $this->videoArray[$meta_key][$videoData['video_key']] = $videoData;
        }

        // Mediatag::notice("Video List {0}",[count($this->videoArray['file'])]);
        return $this->videoArray;
    }

    // public function exec($option = null)
    // {
    //     utmdd($this->VideoList);
    // }

    public function print() {}

    public function getNumberofFiles()
    {
        // utminfo([self::$index++ => [__FILE__,__LINE__,__METHOD__]]);

        $this->getVideoArray();
        $total = count($this->videoArray['file']);
        self::$output->writeLn('<info>There are ' . $total . ' files found</info>');
    }

    public function runCommand()
    {
        // utminfo(func_get_args());

        $array   = $this->commandList;
        $default = $this->default;
        if (isset($this->defaultCommands)) {
            $default = $this->defaultCommands;
        }

        foreach (Option::getOptions() as $option => $value) {
            if (array_key_exists($option, $array)) {
                $cmd = $option;

                foreach ($array[$option] as $method => $args) {
                    if ($args !== null) {
                        if ($args == 'default') {
                            $default = [$method => null];

                            continue;
                        }
                        $commandArgs = Option::getValue($cmd);

                        if (is_array($commandArgs)) {
                            if (array_key_exists(0, $commandArgs)) {
                                if ($args == 'isset') {
                                    $Commands[$method] = $commandArgs[0];

                                    continue;
                                }
                            }
                        }
                        $args = $commandArgs;
                    }

                    $Commands[$method] = $args; // => $value];

                    // utmdd( [$Commands[$method],$method,$args] );

                    if ($method == 'default') {
                        unset($Commands[$method]);
                        $Commands = array_merge($Commands, $default);
                    }
                }
            }
        }

        if (! isset($Commands)) {
            $Commands = $default;
        }

        return $Commands;
    }
}
