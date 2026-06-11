<?php

namespace Mediatag\Modules\Filesystem\Traits;

use Mediatag\Core\MediaCommand;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Database\StorageDB;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Traits\AutoWrapper;
use Mediatag\Utilities\MediaArray;
use Mediatag\Utilities\ScriptWriter;
use Mediatag\Utilities\Strings as UtilitiesStrings;
use ParentClass;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;
use Symfony\Component\Finder\Finder as SFinder;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Debug\UtmStopWatch;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function is_array;

trait ScriptWriterHelper
{
    public function scriptNewFiles($file_array)
    {
        // utminfo(func_get_args());

        if (count($file_array) > 0) {
            $this->NewFilesCommandScript($file_array,
                [
                    'filename' => 'UpdateNewFiles.sh',
                    'command'  => 'update',
                    'options'  => ['update', '-f'],
                ]);
            $this->NewFilesCommandScript($file_array,
                [
                    'filename' => 'ClearNewfiles.sh',
                    'command'  => 'update',
                    'options'  => ['clear', '-f'],
                ]);

            $this->NewFilesCommandScript($file_array,
                ['filename'       => 'AddtoDb.sh',
                    'commandList' => [
                        [
                            'command'  => 'mediadb',
                            'options'  => [],
                            'addfiles' => false,
                        ],
                        [
                            'command'  => 'mediadb',
                            'options'  => ['both'],
                            'addfiles' => false,
                        ],
                    ],
                ]
            );
            $this->NewFilesCommandScript($file_array,
                [
                    'filename' => 'RemoveFromDb.sh',
                    'command'  => 'mediadb',
                    'options'  => ['remove', '-f'],
                ]);
            $this->NewFilesCommandScript($file_array,
                ['filename'       => 'jsonDB.sh',
                    'commandList' => [
                        [
                            'command'  => 'mediadb',
                            'options'  => ['json'],
                            'addfiles' => false,
                        ],
                        [
                            'command'  => 'mediadb',
                            'options'  => ['json', '-u'],
                            'addfiles' => false,
                        ],
                    ],
                ]
            );
        }
    }

    private function NewFilesCommandScript($file_array, $options)
    {
        // utmdump($options);
        $obj = new ScriptWriter($options['filename'], __CURRENT_DIRECTORY__);

        if (array_key_exists('commandList', $options)) {
            foreach ($options['commandList'] as $newOption) {
                $obj = $this->addCmdBody($obj, $file_array, $newOption);
            }
        } else {
            $obj = $this->addCmdBody($obj, $file_array, $options);
        }

        $obj->write();
        Mediatag::$output->writeln('Wrote ' . $options['filename'] . ' script');
    }

    private function addCmdBody($obj, $file_array, $options)
    {
        $addFiles = $options['addfiles'] ?? true;
        $cmd      = 'addCmd';
        $fileCmd  = 'addFiles';

        if (Option::isTrue('append')) {
            $cmd     = 'appendCmd';
            $fileCmd = 'appendFiles';
        }
        $obj->$cmd($options['command'], $options['options'], true, $addFiles);

        if ($addFiles === true) {
            foreach ($file_array as $i => $missing_file) {
                $obj->addFile($missing_file, false);
            }
            $obj->$fileCmd();
        }
        // utmdd($obj->script_text);

        return $obj;
    }
}
