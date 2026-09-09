<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Passwordbox extends \Mediatag\Bundle\Dialog\Widgets\Inputbox
{
    public function __construct(string $text, string $init = '')
    {
        parent::__construct($text, $init);
        $this->widget = 'passwordbox';
    }
}
