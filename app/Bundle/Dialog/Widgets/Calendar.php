<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Calendar extends \Mediatag\Bundle\Dialog\Options\Box
{
    protected $date;
    
    public function __construct(string $text, \DateTime $date = null)
    {
        parent::__construct('calendar', $text, 0, 0);
        $this->date = (is_null($date))? new \DateTime() : $date;
    }
    
    public function parseToString(): string
    {
        return $box_options = parent::parseToString()." {$this->date->format('d')} {$this->date->format('m')} {$this->date->format('Y')}";
    }
    
    public function date(): \DateTime
    {
        if ($this->common_options->date_format === null) {
            $date_format = 'd/m/Y';
        } else {
            $date_format = $this->common_options->date_format;
        }
        
        return date_create_from_format($date_format, trim($this->output));
    }
}
