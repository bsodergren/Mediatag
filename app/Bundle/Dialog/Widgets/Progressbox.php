<?php
namespace Mediatag\Bundle\Dialog\Widgets;

//class Progressbox extends \Mediatag\Bundle\Dialog\Options\Box
class Progressbox extends \Mediatag\Bundle\Dialog\Widgets\Programbox
{
//    protected $cmd = '';
//    public function __construct(string $cmd, string $text = '')
//    {
//        parent::__construct('progressbox', $text, 0, 0);
//        $this->cmd = $cmd;
//    }
//    
//    public function parseToString(): string
//    {
//        return $box_options = parent::parseToString();
//        
//    }
//    
//    public function cmd(): string
//    {
//        return $this->cmd;
//    }
    public function __construct(string $cmd, string $text = '')
    {
        parent::__construct($cmd, $text);
        $this->widget = 'progressbox';
    }
}
