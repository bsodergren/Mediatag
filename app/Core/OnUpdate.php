<?php

namespace Mediatag\Core;

use Composer\Installer\PackageEvent;
use DirectoryIterator;
use RuntimeException;
use UnexpectedValueException;

class OnUpdate
{
    public static function execute()
    {
        define('__ROOT_DIRECTORY__', getcwd());
        define('__APP_HOME__', getcwd());
        define('__CONFIG_LIB__', __ROOT_DIRECTORY__ . '/config');
        require __CONFIG_LIB__ . '/path_constants.php';

        $files = self::get_filelist(\__LOGFILE_DIR__, daysOld: 0);
        foreach($files as $file){
            unlink( $file );
                    }

    }

    public static function get_filelist($path, $ext = 'log', $basename = false, $daysOld = null)
    {
        $directoryPath = $path; // Change to your target directory

        $files_array = [];
        try {
            // Validate directory
            if (! is_dir($directoryPath)) {
                throw new RuntimeException("Directory does not exist: $directoryPath");
            }

            $now = time();
            if (! is_null($daysOld)) {
                $cutoff = $now - ($daysOld * 24 * 60 * 60); // Convert days to seconds
            }

            $dir = new DirectoryIterator($directoryPath);

            foreach ($dir as $fileInfo) {
                // Skip . and ..
                if ($fileInfo->isDot()) {
                    continue;
                }

                // Only process files (skip subdirectories)
                if ($fileInfo->isFile()) {
                    $filePath  = $fileInfo->getPathname();
                    $fileMTime = $fileInfo->getMTime();
                    if (strpos($fileInfo->getFilename(), $ext) > 0) {
                        if (! is_null($daysOld)) {
                            if ($fileMTime > $cutoff) {
                                continue;
                            }
                        }
                        $files_array[] = $filePath;
                    }
                } else {
                    $files_array = array_merge($files_array, self::get_filelist($fileInfo->getPathname(), $ext, $basename, $daysOld));
                }
            }
        } catch (UnexpectedValueException $e) {
            echo 'Error opening directory: ' . $e->getMessage() . "\n";
        } catch (RuntimeException $e) {
            echo 'Runtime error: ' . $e->getMessage() . "\n";
        }

        return $files_array;
    }
}
