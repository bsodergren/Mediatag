<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Export;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\TagBuilder\TagBuilder;
use Mediatag\Modules\TagBuilder\TagReader;
use Nette\Utils\FileSystem;

trait ExportHelper
{
    public static $EXPORT_DIR = __PLEX_VAR_DIR__ . DIRECTORY_SEPARATOR . 'export';

    public function exportMethod($option = null)
    {
        $this->console   = Mediatag::$Display->BarSection1;
        $this->updates   = Mediatag::$Display->BarSection2;
        $this->VideoList = parent::getVideoArray();

        $this->exportMetaData();
    }

    public function getVideoMetaData()
    {
        $FileArray = [];
        foreach ($this->VideoList['file'] as $key => $VideoFile) {
            $tagList = (new TagReader)->loadVideo($VideoFile);

            $VideoFile['Meta'] = $tagList;

            $FileArray[$key] = $VideoFile;
        }

        return $FileArray;
    }

    public function exportMetaData($options = null)
    {
        $fileArray = $this->getVideoMetaData();
        foreach ($fileArray as $key => $data) {
            $this->console->overwrite('Reading file ' . basename($data['video_file']));

            $json_path = self::$EXPORT_DIR . DIRECTORY_SEPARATOR . MediaFile::videoPath($data['video_path']);
            $json_file = $json_path . DIRECTORY_SEPARATOR . $key . '.info.json';
            if (! is_dir($json_path)) {
                FileSystem::createDir($json_path);
            }
            $tagArray    = $this->createJsonData($data['Meta']);
            $json_string = json_encode($tagArray);
            if (! file_exists($json_file)) {
                $method = 'WriteJsonFile';
            } else {
                $method = 'compareExportedFile';
            }
            $this->$method($json_file, $json_string);
        }
    }

    private function createJsonData($tagBuilder)
    {
        $meta = $tagBuilder->getMetaValues();
        $db   = $tagBuilder->getDbValues();
        $json = $tagBuilder->getJsonValues();

        $newTags = TagBuilder::mergetags($meta, $db);

        $newTags = TagBuilder::mergetags($newTags, $json);

        return $newTags;
    }

    private function WriteJsonFile($file, $content)
    {
        MediaFilesystem::writeFile($file, $content, false);
        $this->updates->overwrite('Newjson  file');
    }

    private function compareExportedFile($file, $content)
    {
        $fileContent = MediaFilesystem::readLines($file);
        $jsonArray   = json_decode($fileContent[0], 1);
        $newArray    = json_decode($content, 1);

        $tags = TagBuilder::compareTags($jsonArray, $newArray);
        if (count($tags) > 0) {
            $newTags = TagBuilder::mergetags($jsonArray, $tags);

            MediaFilesystem::writeFile($file, json_encode($newTags), true);
            $this->updates->overwrite('updated file');
            // utmdd(['json File'  => $jsonArray,
            //     'FileMeta'      => $newArray,
            //     'Compared Tags' => $tags,
            //     'Merged Tags'   => $newTags]);
        }
    }
}
