<?php
require __DIR__.'/../vendor/autoload.php';

$common= new \Mediatag\Bundle\Dialog\Options\Common(
    ['backtitle', 'Testing Dialog...']
);

$cmd = 'ls';
$text = 'running ls';
$box = new \Mediatag\Bundle\Dialog\Widgets\Prgbox($cmd, $text);
//$box = new \Mediatag\Bundle\Dialog\Widgets\Prgbox($cmd);
$box->height(20);
$box->width(80);

$dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);
$dialog->run();