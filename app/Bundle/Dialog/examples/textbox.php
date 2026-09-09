<?php
require __DIR__.'/../vendor/autoload.php';

$common= new \Mediatag\Bundle\Dialog\Options\Common(
    ['backtitle', 'Testing Dialog...']
);

$box = new \Mediatag\Bundle\Dialog\Widgets\Textbox(__FILE__);

$dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);
$dialog->run();