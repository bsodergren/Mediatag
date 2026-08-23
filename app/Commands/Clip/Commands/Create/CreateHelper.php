<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Create;

/*
 * Command like Metatag writer for video files.
 */

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\MediaIndicator;

use function count;

trait CreateHelper
{
    public function createClips()
    {
        $this->progress = new MediaIndicator('one');

        // utmdd(count($this->markerArray));
        foreach ($this->markerArray as $i => $fileRow) {
            foreach ($fileRow as $K => $FILE) {
                $filename = $FILE['filename'];
                if (count($FILE['markers']) > 0) {
                    Mediatag::$output->writeln('<comment>' . $this->FileIdx-- . '</> <fg=green>' . basename($filename) . '</>');
                    foreach ($FILE['markers'] as $idx => $marker) {
                        $this->ffmpegCreateClip($filename, $marker, $idx);
                    }
                    Mediatag::$output->writeln('<comment>done</>');

                }
            }
        }
    }
}
