<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Timebox extends \Mediatag\Bundle\Dialog\Options\Box
{
    protected $date;
    
    public function __construct(string $text, \DateTime $date = null)
    {
        parent::__construct('timebox', $text, 0, 0);
        $this->date = (is_null($date))? new \DateTime() : $date;
    }
    
    public function parseToString(): string
    {
        return $box_options = parent::parseToString()." {$this->date->format('H')} {$this->date->format('i')} {$this->date->format('s')}";
    }
    
    public function time(): \DateTime
    {
        if ($this->common_options->time_format === null) {
            $time_format = 'H:i:s';
        } else {
            $time_format = $this->common_options->time_format;
        }
        
        return date_create_from_format($time_format, trim($this->output));
    }
}
