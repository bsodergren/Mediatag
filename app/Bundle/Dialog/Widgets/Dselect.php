<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Dselect extends \Mediatag\Bundle\Dialog\Options\Box
{
    
    public function __construct(string $filepath)
    {
        parent::__construct('dselect', $filepath, 0, 0);
    }
    
    public function parseToString(): string
    {
        return $box_options = parent::parseToString();
    }
    
    public function directory(): string
    {
        return $this->output();
    }
}
