<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Infobox extends \Mediatag\Bundle\Dialog\Options\Box
{
    
    public function __construct(string $text)
    {
        parent::__construct('infobox', $text, 0, 0);
    }
    
    public function parseToString(): string
    {
        return $box_options = parent::parseToString();
    }
}
