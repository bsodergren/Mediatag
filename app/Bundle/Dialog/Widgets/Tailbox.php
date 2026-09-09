<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Bundle\Dialog\Widgets;

class Tailbox extends \Mediatag\Bundle\Dialog\Options\Box
{
    public function __construct(string $file)
    {
        parent::__construct('tailbox', $file, 0, 0);
    }

    public function parseToString(): string
    {
        return "--{$this->widget} '{$this->text}' {$this->height} {$this->width}";
    }
}
