<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable;

use Mediatag\Core\Mediatag;
use UTM\Utilities\Debug\UtmStopWatch;
use Mediatag\Core\MediaCache;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Traits\Callables;
use Nette\Utils\Callback;

class ReadMeta extends MediatagExec
{
    use Callables;

    public $execMode;

    public function __construct($videoData, $input = null, $output = null)
    {
        // utminfo($videoData);

        $this->execMode = 'read';
        parent::__construct($videoData, $input, $output);
    }

    public function read()
    {
        // utminfo(func_get_args());

        $video_key = File::file($this->video_file, 'videokey');
        $array     = MediaCache::get($video_key);
        if (false === $array) {
            $command  = [
                Mediatag::App(),
                $this->video_file,
                '-t',
            ];

            $callback = Callback::check([$this, 'ReadMetaOutput']);
            UtmStopWatch::lap(__METHOD__ . ' ' . __LINE__, $command);

            $this->exec($command, $callback);
            UtmStopWatch::lap(__METHOD__ . ' ' . __LINE__, '');

            $array    = [
                $this->video_key => [
                    'video_file'    => $this->video_file,
                    'video_path'    => $this->video_path,
                    'video_name'    => $this->video_name,
                    'video_library' => $this->video_library,
                    'metatags'      => $this->metatags,
                ],
            ];

            if (\count($this->metatags) > 0) {
                MediaCache::put($video_key, $array);
            }
        }

        return $array;
    }

    private function getMetaValue($text)
    {
        // utminfo(func_get_args());

        return preg_replace_callback_array([
            '/.*(alb).*contains\:\ (.*)/' => function ($matches) {
                return $this->metatags['studio'] = $matches[2];
            },
            '/(gen).*contains\:\ (.*)/'   => function ($matches) {
                return $this->metatags['genre'] = $matches[2];
            },
            '/(nam).*contains\:\ (.*)/'   => function ($matches) {
                return $this->metatags['title'] = $matches[2];
            },
            '/(aART).*contains\:\ (.*)/'  => function ($matches) {
                return $this->metatags['artist'] = $matches[2];
            },
            '/(keyw).*contains\:\ (.*)/'  => function ($matches) {
                return $this->metatags['keyword'] = $matches[2];
            },
            '/(tvnn).*contains\:\ (.*)/'  => function ($matches) {
                return $this->metatags['network'] = $matches[2];
            },
        ], $text);
    }
}
