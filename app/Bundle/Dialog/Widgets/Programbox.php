<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Programbox extends \Mediatag\Bundle\Dialog\Options\Box
{
    protected $cmd = '';
    public function __construct(string $cmd, string $text = '')
    {
        parent::__construct('programbox', $text, 0, 0);
        $this->cmd = $cmd;
    }
    
    public function parseToString(): string
    {
        return $box_options = parent::parseToString();
    }
    
    public function cmd(): string
    {
        return $this->cmd;
    }
}
