<?php
require __DIR__.'/../vendor/autoload.php';

$common= new \Mediatag\Bundle\Dialog\Options\Common(
    ['backtitle', 'Testing Dialog...']
);

$box = new \Mediatag\Bundle\Dialog\Widgets\Msgbox('It is an msgbox\nDialog & PHP is cool!');

$dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);
$dialog->run();