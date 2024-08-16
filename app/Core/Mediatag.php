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
use Mediatag\Traits\CmdProcess;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Option;

abstract class Mediatag extends Command
{
    use CmdProcess;

    public const PH_META_CACHE = __CACHE_DIR__ . '/pornhub.hash';

    public static $SearchArray = [];

    public static $finder;

    public static $filesystem;

    public static $Console;

    public static $Display;

    public static $dbconn;

    public $video_name;

    public $videoArray;

    public static $output;

    public $command;

    public static $input;

    public $helper;
    public $actions;
    // public static $keywordList = __DATA_LISTS__.'/keyword.txt';

    // public static $genreList = __DATA_LISTS__.'/genre.txt';

    // public static $titleList = __DATA_LISTS__.'/title.txt';

    // public static $artistList = __DATA_LISTS__.'/ArtistList.txt';

    // public static $titlesMap = __DATA_LISTS__.'/TitlesList.txt';

    // public static $ignoreList = __DATA_LISTS__.'/NamesList.txt';

    public static $amateurFile = __DATA_MAPS__ . '/Amateur.txt';

    public static $channelFile = __DATA_MAPS__ . '/Channels.txt';

    // public static $keywordReplacement = __DATA_MAPS__.'/keyword.txt';

    // public static $genreReplacement = __DATA_MAPS__.'/genre.txt';

    // public static $titleMap = __DATA_MAPS__.'/title.txt';

    public static $title_map;
    public static $IoStyle;

    public static $tmpText     = null;

    private $meta_tag_arrary   = [
        'studio',
        'genre',
        'artist',
        'title',
        'keyword',
    ];

    public function __construct(InputInterface $input = null, OutputInterface $output = null, $args = null)
    {
        self::boot($input, $output);
    }

    public static function __callStatic($method, $args): string
    {
        if ('GetApp' == $method) {
            if (file_exists(CONFIG['ATOMICPARSLEY'])) {
                return CONFIG['ATOMICPARSLEY'];
            }

            exit(CONFIG['ATOMICPARSLEY'] . ' does not exist');
        }
    }

    public function boot(InputInterface $input = null, OutputInterface $output = null, $options = [])
    {
        if (! \defined('__CURRENT_DIRECTORY__')) {
        \define('__CURRENT_DIRECTORY__', getcwd());
        }

        if(count($options) > 0) {
            foreach ($options as $key => $value) {
                // Option::set($key, $value);
            }
        }



        $this->command            = self::getDefaultName();
        self::$input              = $input;
        self::$output             = $output;
        MediaCache::init($input, $output);

        Option::init($input);
        self::$Console            = new ConsoleOutput($output, $input);
        foreach (Option::getOptions() as $option => $v) {
            switch ($option) {
                case 'title':
                case 'genre':
                case 'artist':
                case 'keyword':
                case 'studio':
                    if (! \defined('__UPDATE_SET_ONLY__')) {
                        \define('__UPDATE_SET_ONLY__', true);
                    }
                    $this->meta_tag_arrary = [$option];

                    break;
            }
        }
        if (true == Option::isTrue('only')) {
            $this->meta_tag_arrary = Option::getValue('only');
        }

        if (true == Option::isTrue('empty')) {
            if (null !== Option::getValue('empty', 1)) {
                $this->meta_tag_arrary = Option::getValue('empty');
            }
        }
        if (! \defined('__META_TAGS__')) {
            \define('__META_TAGS__', $this->meta_tag_arrary);
        }

        UTMLog::Logger('Meta Tags', __META_TAGS__);

        self::$Display            = new Display($output);

        self::$dbconn             = new StorageDB($input, $output);
        $this->StorageConn        = new Storage();

        self::$finder             = new Finder();
        self::$filesystem         = new Filesystem();
        self::$finder->defaultCmd = $this->command ;
        if(!Option::isTrue('SKIP_SEARCH')) {

            self::$SearchArray        = self::$finder->ExecuteSearch();

            if (true == Option::isTrue('numberofFiles')) {
                $this->getNumberofFiles();
                exit;
            }
        }

        if (! \defined('TITLE_REPLACE_MAP')) {
            $this->getTitleMap('TITLE_REPLACE_MAP', $this->StorageConn->getTitleMap());
        }
    }

    public function process()
    {
        $ClassCmds = $this->runCommand();

        foreach ($ClassCmds as $cmd => $option) {
            if (method_exists($this, $cmd)) {
                $this->{$cmd}($option);
            } else {
                self::$output->writeln('<info>' . $cmd . ' doesnt exist</info>');

                return 0;
            }
        }
    }

    public static function App(): string
    {
        if (file_exists(CONFIG['ATOMICPARSLEY'])) {
            return CONFIG['ATOMICPARSLEY'];
        }

        exit(CONFIG['ATOMICPARSLEY'] . ' does not exist');
    }

    public function getVideoArray()
    {
        $file_array               = self::$SearchArray;
        $this->videoArray['file'] = [];
        $this->videoArray['dupe'] = [];
        $count                    = \count($file_array);


        self::$output->writeln('<info>Getting Video array</info>');
        foreach ($file_array as $__ => $file) {
            $fs                                                   = new File($file);
            $videoData                                            = $fs->get();

            if (! \array_key_exists($videoData['video_key'], $this->videoArray['file'])) {
                $meta_key = 'file';
            } else {
                $meta_key = 'dupe';
            }
            $this->videoArray[$meta_key][$videoData['video_key']] = $videoData;
        }

        return $this->videoArray;
    }

    public function getTitleMap($constant, $file)
    {
        if (\is_string($file)) {
            if (is_file($file)) {
                $artistList = file_get_contents($file);

                $artistMap  = explode("\n", $artistList);
            }
        } else {
            $artistMap = $file;
        }

        foreach ($artistMap as $name) {
            $name        = trim($name);
            $nameArray[] = strtolower($name);
        }

        sort($nameArray);
        array_unique($nameArray);
        if (! \defined($constant)) {

        \define($constant, $nameArray);
        }
    }

    public function exec($option = null) {}

    public function print() {}

    public function getNumberofFiles()
    {
        $this->getVideoArray();
        $total = \count($this->videoArray['file']);
        self::$output->writeLn('<info>There are ' . $total . ' files found</info>');
    }
}
