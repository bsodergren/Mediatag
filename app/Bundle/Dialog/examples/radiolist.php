<?php
require __DIR__.'/../vendor/autoload.php';

$common= new \Mediatag\Bundle\Dialog\Options\Common(
    ['backtitle', 'Testing Dialog...']
);

$box = new \Mediatag\Bundle\Dialog\Widgets\Radiolist('Select items:', 0,
    new \Mediatag\Bundle\Dialog\Options\Item(1, 'Item one'),
    new \Mediatag\Bundle\Dialog\Options\Item(2, 'Item two'),
    new \Mediatag\Bundle\Dialog\Options\Item(3, 'Item three', true),
    new \Mediatag\Bundle\Dialog\Options\Item(4, 'Item four'),
    new \Mediatag\Bundle\Dialog\Options\Item('textual item', 'Another item')
);

$dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);
$dialog->run();
echo PHP_EOL, 'Output is: ', $dialog->output(), PHP_EOL;
echo PHP_EOL, 'Items selected are:', PHP_EOL;
print_r($box->item());
