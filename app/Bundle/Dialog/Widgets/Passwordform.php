<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Passwordform extends \Mediatag\Bundle\Dialog\Widgets\Form
{
    public function __construct(string $text, int $formheight = 0, \Mediatag\Bundle\Dialog\Options\Field ...$items)
    {
        parent::__construct($text, $formheight, ...$items);
        $this->widget = 'passwordform';
    }
}
