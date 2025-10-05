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

    public $format = '%current:4s%/%max:4s% ,[%bar%], %percent:3s%%';

    public function __construct($count, $section = null, $width = 50)
    {
        // utminfo(func_get_args());

        $this->width = $width;

        if (null === $section) {
            $section = Mediatag::$output;
        } else {
            $section = Mediatag::$output->section();
        }

        $this->count = $count;
        $this->bar   = new ProgressBar($section, $count);
    }

    public static function addFormat($format = '<comment>%message:10s%</comment> %current:4s%/%max:4s% [%bar%] %percent:3s%%', $name = 'custom')
    {
        // utmdump($format);
        ProgressBar::setFormatDefinition($name, $format);
    }

    public function setMsgFormat($name = 'custom')
    {
        // utminfo(func_get_args());

        // ProgressBar::setFormatDefinition($name, $format);
        $this->format = $name;
        $this->bar->setFormat($this->format);

        return $this;
    }

    public function setMessage($message, $name)
    {
        // utminfo(func_get_args());

        // if ($this->format != 'custom') {
        //     $this->setMsgFormat();
        // }
        $this->bar->setMessage($message, $name);

        return $this;
    }

    public function newBar($bar = '<comment>-</comment>', $lead = '<error>></error>')
    {
        // utminfo(func_get_args());

        // $this->bar->setBarCharacter($bar);
        // $this->bar->setProgressCharacter($lead);

        $this->bar->setRedrawFrequency(100);
        $this->bar->maxSecondsBetweenRedraws(0.2);
        $this->bar->minSecondsBetweenRedraws(0.1);

        $this->bar->setBarWidth($this->width);

        return $this;
    }

    public function start()
    {
        // utminfo(func_get_args());

        $this->bar->start();
    }

    public function advance($int = 1)
    {
        // utminfo(func_get_args());

        $this->bar->advance($int);
    }

    public function clear()
    {
        // utminfo(func_get_args());

        $this->bar->clear();
    }
}
