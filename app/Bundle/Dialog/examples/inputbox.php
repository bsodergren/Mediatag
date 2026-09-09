<?php
require __DIR__.'/../vendor/autoload.php';

$common= new \Mediatag\Bundle\Dialog\Options\Common(
    ['backtitle', 'Testing Dialog...']
);

$box = new \Mediatag\Bundle\Dialog\Widgets\Inputbox('Inputbox sample', 'Default value');

$dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);
$dialog->run();

echo PHP_EOL, 'Output is: ', $dialog->output(), PHP_EOL;