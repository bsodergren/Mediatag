<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable;

use Mediatag\Core\MediaCache;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\Callbacks\ProcessCallbacks;
use Nette\Utils\Callback;

class ReadMeta extends MediatagExec
{
    use ProcessCallbacks;
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

        $array = MediaCache::get($this->video_key);
        // $array = false;
        if (false === $array) {
            $command = [
                Mediatag::App(),
                $this->video_file,
                '-t',
            ];

            $callback = Callback::check([$this, 'ReadMetaOutput']);

            $this->exec($command, $callback);

            $array = [
                $this->video_key => [
                    'video_file'    => $this->video_file,
                    'video_path'    => $this->video_path,
                    'video_name'    => $this->video_name,
                    'video_library' => $this->video_library,
                    'metatags'      => $this->metatags,
                ],
            ];

            // if (\count($this->metatags) > 0) {
            MediaCache::put($this->video_key, $array);
            // }
        }

        return $array;
    }

    public function getMetaValue($text)
    {
        // utminfo(func_get_args());
        $return = preg_replace_callback_array([
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

        // utmdump([$return,$text]);
        return $return;
    }
}
