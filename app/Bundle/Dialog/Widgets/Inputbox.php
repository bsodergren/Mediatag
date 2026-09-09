<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Inputbox extends \Mediatag\Bundle\Dialog\Options\Box
{
    protected $init = '';
    public function __construct(string $text, string $init = '')
    {
        parent::__construct('inputbox', $text, 0, 0);
        $this->init = $init;
    }
    
    public function parseToString(): string
    {
        return $box_options = parent::parseToString()." '{$this->init}'";
    }
}
