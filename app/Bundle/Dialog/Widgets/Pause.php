<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Pause extends \Mediatag\Bundle\Dialog\Options\Box
{
    protected $seconds = 0;
    public function __construct(string $text, int $seconds)
    {
        parent::__construct('pause', $text, 0, 0);
        $this->seconds = $seconds;
    }
    
    public function parseToString(): string
    {
        return $box_options = parent::parseToString()." {$this->seconds}";
    }
}
