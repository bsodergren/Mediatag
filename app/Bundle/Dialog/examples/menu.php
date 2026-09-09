<?php
require __DIR__.'/../vendor/autoload.php';

$common= new \Mediatag\Bundle\Dialog\Options\Common(
    ['backtitle', 'Testing Dialog...']
);

$box = new \Mediatag\Bundle\Dialog\Widgets\Menu('Change items:', 0,
    new \Mediatag\Bundle\Dialog\Options\MenuItem(1, 'Item one'),
    new \Mediatag\Bundle\Dialog\Options\MenuItem(2, 'Item two'),
    new \Mediatag\Bundle\Dialog\Options\MenuItem(3, 'Item three'),
    new \Mediatag\Bundle\Dialog\Options\MenuItem(4, 'Item four'),
    new \Mediatag\Bundle\Dialog\Options\MenuItem('textual item', 'Another item')
);

$dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);
$dialog->run();
echo PHP_EOL, 'Output is: ', $dialog->output(), PHP_EOL;
echo PHP_EOL, 'Item selected is: ', $box->selected(), PHP_EOL;
