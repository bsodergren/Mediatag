<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Textbox extends \Mediatag\Bundle\Dialog\Options\Box
{
    
    public function __construct(string $filepath)
    {
        parent::__construct('textbox', $filepath, 0, 0);
    }
    
    public function parseToString(): string
    {
        return $box_options = parent::parseToString();
    }
}
