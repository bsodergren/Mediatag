<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Editbox extends \Mediatag\Bundle\Dialog\Options\Box
{
    
    public function __construct(string $filepath)
    {
        parent::__construct('editbox', $filepath, 0, 0);
    }
    
    public function parseToString(): string
    {
        return $box_options = parent::parseToString();
    }
    
    public function content(): string
    {
        return $this->output();
    }
}
