<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Prgbox extends \Mediatag\Bundle\Dialog\Options\Box
{
    protected $cmd = '';
    public function __construct(string $cmd, string $text = '')
    {
        parent::__construct('prgbox', $text, 0, 0);
        $this->cmd = $cmd;
    }
    
    public function parseToString(): string
    {
        $text = ($this->text === '')? '' : $this->text;
        return "--{$this->widget} '{$text}' '{$this->cmd}' {$this->height} {$this->width}";
    }
}
