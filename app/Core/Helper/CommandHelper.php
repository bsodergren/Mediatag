<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use Symfony\Component\Filesystem\Filesystem;

trait CommandHelper
{
    protected function loadDirs()
    {
        $filesystem = new Filesystem();
        foreach (__CREATE_DIRS__ as $dir) {
            if (!is_dir($dir)) {
                $filesystem->mkdir($dir);
            }
        }
    }

    public static function getProcessClass()
    {
        $className  = static::class;
        $pathInfo   = explode('\\', $className);
        $pathInfo   = \array_slice($pathInfo, 0, 3);
        $pathInfo[] = 'Process';
        $className  = implode('\\', $pathInfo);

        return $className;
    }
}
