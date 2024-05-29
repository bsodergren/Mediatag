<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Display;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Helper\ProgressBar;

class MediaBar
{
    public static $display = true;

    public $section;

    public $width;

    public $output;

    public $count;

    public $bar;
    public $sectionName;

    public function __construct($count, $section = null, $width = 50)
    {
        $this->width = $width;

        if (null === $section) {
            $section = Mediatag::$output;
        } else {
            $section = Mediatag::$output->section();
        }

        $this->count = $count;
        $this->bar = new ProgressBar($section, $count);
    }

    public function newBar($bar = '<comment>-</comment>', $lead = '<error>></error>')
    {
        // $this->bar->setBarCharacter($bar);
        // $this->bar->setProgressCharacter($lead);
        $this->bar->setFormat('%current:4s%/%max:4s% [%bar%] %percent:3s%%');

        $this->bar->setRedrawFrequency(100);
        $this->bar->maxSecondsBetweenRedraws(0.2);
        $this->bar->minSecondsBetweenRedraws(0.1);

        $this->bar->setBarWidth($this->width);

        return $this;
    }

    public function start()
    {
        $this->bar->start();
    }

    public function advance()
    {
        $this->bar->advance();
    }

    public function clear()
    {
        $this->bar->clear();
    }
}
