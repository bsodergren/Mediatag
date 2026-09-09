<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Fselect extends \Mediatag\Bundle\Dialog\Options\Box
{
    
    public function __construct(string $filepath)
    {
        parent::__construct('fselect', $filepath, 0, 0);
    }
    
    public function parseToString(): string
    {
        return $box_options = parent::parseToString();
    }
    
    public function selection(): string
    {
        return $this->output();
    }
}
