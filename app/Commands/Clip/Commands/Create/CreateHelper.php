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

trait CreateHelper
{
    public function createClips()
    {
        $this->progress = new MediaIndicator('one');

        foreach ($this->markerArray as $i =>$fileRow) {
            foreach ($fileRow as $K =>$FILE) {
                $filename = $FILE['filename'];

                if (\count($FILE['markers']) > 0) {
                    foreach ($FILE['markers'] as $idx =>$marker) {
                        Mediatag::$output->writeln('<comment>'.$this->FileIdx--.'</> <fg=green>'.basename($filename).'</>');
                        // $frame_json   = $this->ffmprobeGetFrames($filename, $marker['start'], $marker['end']);
                        // $this->frames = $frame_json['streams'][0]['nb_read_frames'];
                        $this->ffmpegCreateClip($filename, $marker, $idx);
                    }
                }
            }
        }
    }
}
