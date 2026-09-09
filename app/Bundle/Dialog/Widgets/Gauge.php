<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Gauge extends \Mediatag\Bundle\Dialog\Options\Box
{
    protected $percent = 0;
    protected $updater = null;
    public function __construct(string $text, callable $updater, int $percent = 0)
    {
        parent::__construct('gauge', $text, 0, 0);
        $this->percent = $percent;
        $this->updater = $updater;
    }
    
    public function parseToString(): string
    {
        return $box_options = parent::parseToString()." {$this->percent}";
    }
    
    public function updater()
    {
        return $this->updater;
    }
}
