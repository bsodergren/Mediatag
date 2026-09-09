<?php
require __DIR__.'/../vendor/autoload.php';

$common= new \Mediatag\Bundle\Dialog\Options\Common(
    ['backtitle', 'Testing Dialog...']
);

$box = new \Mediatag\Bundle\Dialog\Widgets\Tailbox(__FILE__);
$box->height(20);
$box->width(80);

$dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);
$dialog->run();