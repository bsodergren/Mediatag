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

    public $format         = '%current:4s%/%max:4s% [%bar%] %percent:3s%%';

    public function __construct($count, $section = null, $width = 50)
    {
        utminfo();

        $this->width = $width;

        if (null === $section) {
            $section = Mediatag::$output;
        } else {
            $section = Mediatag::$output->section();
        }

        $this->count = $count;
        $this->bar   = new ProgressBar($section, $count);
    }

    public function setMsgFormat($format = '<comment>%message%</comment> %current:4s%/%max:4s% [%bar%] %percent:3s%%')
    {
        utminfo();


        ProgressBar::setFormatDefinition('custom', $format);
        $this->format='custom';
        return $this;

    }

    public function setMessage($message)
    {
        utminfo();

        if ($this->format != 'custom') {
            $this->setMsgFormat();
        }
        $this->bar->setMessage($message);
        return $this;
    }

    public function newBar($bar = '<comment>-</comment>', $lead = '<error>></error>')
    {
        utminfo();

        // $this->bar->setBarCharacter($bar);
        // $this->bar->setProgressCharacter($lead);
        $this->bar->setFormat($this->format);

        $this->bar->setRedrawFrequency(100);
        $this->bar->maxSecondsBetweenRedraws(0.2);
        $this->bar->minSecondsBetweenRedraws(0.1);

        $this->bar->setBarWidth($this->width);

        return $this;
    }

    public function start()
    {
        utminfo();

        $this->bar->start();
    }

    public function advance()
    {
        utminfo();

        $this->bar->advance();
    }

    public function clear()
    {
        utminfo();

        $this->bar->clear();
    }
}
