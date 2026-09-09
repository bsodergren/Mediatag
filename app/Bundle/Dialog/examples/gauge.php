<?php
require __DIR__ . '/../vendor/autoload.php';

$common = new \Mediatag\Bundle\Dialog\Options\Common(
    ['backtitle', 'Testing Dialog...']
);

$box = new \Mediatag\Bundle\Dialog\Widgets\Gauge('Processing...', function($dialog) {
    $p = 0;
    while ($p < 100) {
        sleep(0.1);
        $p++;
        $dialog->write("\n\r$p");
    }
    return;
});

$dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);
$dialog->run();