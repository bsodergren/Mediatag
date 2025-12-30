<?php

namespace Mediatag\Commands\Clip\Commands\Show\Trait;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use UTM\Utilities\Option;

use function array_key_exists;

trait Transitions
{
    public function showTransitionType()
    {
        $array = array_chunk($this->transition_types, 4);

        $tableStyle = new TableStyle;
        $tableStyle->setHorizontalBorderChars('<fg=magenta>-</>');
        $tableStyle->setVerticalBorderChars('<fg=magenta>|</>');
        $tableStyle->setDefaultCrossingChar(' ');

        $table = new Table(Mediatag::$output);
        // $table->setHeaderTitle('Transition types');
        $table->setRows($array);
        $table->setStyle($tableStyle);
        // $table->setStyle('borderless');
        $table->render();
    }
}
