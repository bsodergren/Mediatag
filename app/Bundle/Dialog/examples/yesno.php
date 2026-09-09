<?php
require __DIR__.'/../vendor/autoload.php';

$common= new \Mediatag\Bundle\Dialog\Options\Common(
    ['backtitle', 'Testing Dialog...']
);

$box = new \Mediatag\Bundle\Dialog\Widgets\Yesno('You loves PHP?');

$dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);
$dialog->run();
echo PHP_EOL, 'Output is: ', $dialog->exit_code(), PHP_EOL;