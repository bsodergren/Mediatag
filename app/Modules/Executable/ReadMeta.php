<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable;

use Mediatag\Core\MediaCache;
use Mediatag\Core\Mediatag;
use Mediatag\Entities\MetaEntities;
use Mediatag\Modules\Executable\Callbacks\traits\ProcessCallbacks;
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

    public function read($update = false)
    {
        // utminfo(func_get_args());
        $array = MediaCache::get($this->video_key);
        if ($update === true) {
            $array = false;
        }

        if ($array === false) {
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

            if (\count($this->metatags) > 0) {
                MediaCache::put($this->video_key, $array);
            }
        }

        return $array;
    }

    public function getMetaValue($text)
    {
        $callbackPatterns = (new MetaEntities)->init()->getCallbackArray();

        foreach ($callbackPatterns as $class => $pattern) {
            $class   = strtolower($class);
            $matched = preg_replace_callback($pattern,
                function ($matches) use ($class) {
                    return $this->metatags[$class] = $matches[2];
                },
                $text);
        }
    }
}
